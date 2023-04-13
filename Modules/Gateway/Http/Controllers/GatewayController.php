<?php

namespace Modules\Gateway\Http\Controllers;

use App\Models\Order;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Modules\Gateway\Contracts\RequiresWebHookValidationInterface;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Gateway\Contracts\CryptoResponseInterface;
use Modules\Gateway\Contracts\HasDataResponseInterface;
use Modules\Gateway\Contracts\RequiresCallbackInterface;
use Modules\Gateway\Entities\GatewayModule;
use Modules\Gateway\Entities\PaymentLog;
use Modules\Gateway\Facades\GatewayHandler;
use Modules\Gateway\Redirect\GatewayRedirect;
use Modules\Gateway\Services\GatewayHelper;
use Modules\Gateway\Traits\ApiResponse;
use Cart;
use Cache;
class GatewayController extends Controller
{
    use ApiResponse;

    private $helper;

    public function __construct()
    {
        $this->helper = GatewayHelper::getInstance();
    }


    /**
     * Display payable payment gateway.
     *
     * @return Renderable
     */
    public function paymentGateways(Request $request)
    {
        if (!checkRequestIntegrity()) {
            return redirect(GatewayRedirect::failedRedirect('integrity'));
        }
        $query = $request->query->all() ?? [];
        $purchaseData = $this->helper->getPurchaseData();
        $gateways = (new GatewayModule)->payableGateways();



        $refCode = substr(sha1(time()), 29,40);
        $memberId = Auth::user()->member_id;
        $amount = $purchaseData->total;
        $orderDate = $purchaseData->order_date;
        $savingsApiResponse = $this->postPaymentNotification($refCode, $memberId, $amount, $orderDate, 1);
        $loanApiResponse = $this->postPaymentNotification($refCode, $memberId, $amount, $orderDate, 2);
        $savingsCollection = null;
        if($savingsApiResponse->getStatusCode() == 200) {
            $response_data = json_decode((string)$savingsApiResponse->getBody(), true);
            $savingsCollection = collect($response_data);
        }
        $loanCollection = null;
        if($loanApiResponse->getStatusCode() == 200) {
            $response_data = json_decode((string)$loanApiResponse->getBody(), true);
            $loanCollection = collect($response_data);
        }

        if ($purchaseData->status == 'completed') {
            $message = __('Already paid for the order.');
            return view('gateway::pay', compact('gateways', 'purchaseData', 'message'));
        }

        return view('gateway::pay', compact('gateways', 'purchaseData', 'savingsCollection', 'loanCollection'));
    }


    /**
     * Displays the payment page for specific payment gateway
     *
     * @param \Illuminate\Http\Request
     *
     * @return Renderable
     */
    public function pay(Request $request)
    {
        if (!checkRequestIntegrity()) {
            return redirect(GatewayRedirect::failedRedirect('integrity'));
        }
        if (moduleAvailable($request->gateway) && $this->helper->isModuleActive($request->gateway)) {
            $viewClass = GatewayHandler::getView($request->gateway);
            return $viewClass::paymentView($this->helper->getPaymentCode());
        }
        return redirect(route('gateway.payment', withOldQueryString()))->withErrors(__('Payment method not found.'));
    }


    /**
     * Process the payment for specific gateway
     *
     * @param \Illuminate\Http\Request
     *
     * @return redirect
     */
    public function makePayment(Request $request)
    {
        if (!checkRequestIntegrity()) {
            return redirect(GatewayRedirect::failedRedirect('integrity'));
        }
        if (moduleAvailable($request->gateway)) {
            try {
                $code = $this->helper->getPaymentCode();

                if (!$this->helper->getPaymentLog($code)) {
                    return redirect()->route('site.cart')->withErrors(__(':x does not exist.', ['x' => __('Order')]));
                }

                $processor = GatewayHandler::getProcessor($request->gateway);

                $response = $processor->pay($request);

                if ($processor instanceof RequiresWebHookValidationInterface) {
                    PaymentLog::where('code', $code)->update($this->getUpdateData($response));
                    return redirect($response->getUrl());
                }
                if ($processor instanceof RequiresCallbackInterface) {
                    return $response;
                }
                PaymentLog::where('code', $code)->update($this->getUpdateData($response));
            } catch (\Exception $e) {
                return redirect(route('gateway.payment', withOldQueryIntegrity()))->withErrors($e->getMessage());
            }
            return redirect()->route(techDecrypt(request()->to), withOldQueryIntegrity());
        }
        return redirect(route('gateway.payment', withOldQueryIntegrity()))->withErrors(__('Payment method not available.'));
    }


    /**
     * This function handle response of redirected payment callbacks
     *
     * @param \Illuminate\Http\Request
     *
     * @return redirect
     */
    public function paymentCallback(Request $request)
    {
        try {
            $processor = GatewayHandler::getProcessor($request->gateway);
            $response = $processor->validateTransaction($request);
            $code = $this->helper->getPaymentCode();
            PaymentLog::where('code', $code)->update($this->getUpdateData($response));
            return redirect(route(techDecrypt(request()->to), withOldQueryIntegrity()));
        } catch (\Exception $e) {
            return redirect(route('gateway.payment', withOldQueryIntegrity()))->withErrors($e->getMessage());
        }
    }


    /**
     * Handles cancelled payment request
     *
     * @param \Illuminate\Http\Request
     *
     * @return redirect
     */
    public function paymentCancelled(Request $request)
    {
        try {
            $processor = GatewayHandler::getProcessor($request->gateway);
            $processor->cancel($request);
        } catch (\Exception $e) {
            return redirect(route('gateway.payment'))->withErrors($e->getMessage());
        }
    }

    /**
     * Process payment from gateways which sends response to the hook URL
     *
     * @param \Illuminate\Http\Request
     *
     * @return bool
     */
    public function paymentHook(Request $request)
    {
        try {
            $processor = GatewayHandler::getProcessor($request->gateway);
            $payment = $processor->validatePayment($request);
            if (!$payment) {
                return false;
            }
        } catch (\Exception $e) {
            paymentLog([$e, $request->all()]);
            return false;
        }
        return true;
    }


    /**
     * Process payment response
     *
     * @param \Modules\Gateway\Response\Response
     *
     * @return array
     */
    private function getUpdateData($response)
    {
        $array['gateway'] = $response->getGateway();
        $array['status'] = $response->getStatus();
        if ($response instanceof HasDataResponseInterface) {
            $array['response'] = $response->getResponse();
            $array['response_raw'] = $response->getRawResponse();
        }
        if ($response instanceof CryptoResponseInterface) {
            $array['unique_code'] = $response->getUniqueCode();
        }
        return $array;
    }

    public function postPaymentNotification($refCode, $memberId, $amount, $orderDate, $pMethod = 2){
        try{
            $data = [
                "refcode"=>$refCode,
                "uid"=>$memberId,
                "amount"=>$amount,
                "transdate"=>$orderDate,
                "sid"=>"SRVCOOP120",
                "pmethod"=>$pMethod
            ];
            $url = "https://www.coopeastngr.com/api/mkpay.asp";
            $client = new Client();
            return  $client->request('POST', $url, [
                'json'=>$data]);
        }catch (\Exception $exception){
            return 'exception'.$exception;
        }
    }

    public function sendAPIRequest($url, $data){
        try{

            $client = new Client();
            return  $client->request('POST', $url, [
                'json'=>$data]);
        }catch (\Exception $exception){
            return 'exception'.$exception;
        }
    }

    public function paymentConfirmation(Request $request)
    {
        /*
         * {"id":3,
         * "user_id":2,
         * "reference":"ORD-0003",
         * "note":null,
         * "order_date":"2022-12-17",
         * "currency_id":3,
         * "leave_door":null,
         * "other_discount_amount":"0.00000000",
         * "other_discount_type":null,
         * "shipping_charge":"2.00000000",
         * "tax_charge":"1.26500000",
         * "shipping_title":"Flat Rate",
         * "total":"28.26500000",
         * "paid":"0.00000000",
         * "total_quantity":"1.00000000",
         * "amount_received":"0.00000000",
         * "order_status_id":1,
         * "is_delivery":0,
         * "payment_status":"Unpaid","created_at":"2022-12-17T01:16:02.000000Z","updated_at":null,"currency":{"id":3,"name":"USD","symbol":"$","exchange_rate":null,"exchange_from":null}}
         */
        //Response
        /*
         * {"amount":42.02,
         * "amount_captured":42.02,
         * "currency":"usd",
         * "code":"ORD-0002"}
         */
        //Init call to COOPFin
        $code = techDecrypt($request->code);
        $purchaseData = PaymentLog::where('code', $code)->orderBy('id', 'desc')->first();


        $refCode = substr(sha1(time()), 29,40);
        $memberId = Auth::user()->member_id;
        $amount = $purchaseData->total;
        $orderDate = $purchaseData->order_date;
        $savingsApiResponse = $this->postPaymentNotification($refCode, $memberId, $amount, $orderDate, 1);
        $loanApiResponse = $this->postPaymentNotification($refCode, $memberId, $amount, $orderDate, 2);
        $savingsCollection = null;
        if($savingsApiResponse->getStatusCode() == 200) {
            $response_data = json_decode((string)$savingsApiResponse->getBody(), true);
            $savingsCollection = collect($response_data);
        }
        $loanCollection = null;
        if($loanApiResponse->getStatusCode() == 200) {
            $response_data = json_decode((string)$loanApiResponse->getBody(), true);
            $loanCollection = collect($response_data);
        }
        return view("gateway::confirmation",[
            'purchaseData'=>$purchaseData,
            'savingsCollection'=>$savingsCollection,
            'loanCollection'=>$loanCollection,
        ]);
    }

    public function paymentFailed(Request $request)
    {
        $errors = [
            'integrity' => __("Invalid payment request authentication failed. Please retry payment from the start."),
            'error' => __("Payment processing failed.")
        ];
        $data = [];
        if (isset($errors[$request->error])) {
            $data['message'] = $errors[$request->error];
        }
        return view("gateway::failed-payment", $data);
    }


    public function coopsavingsConfirmation(Request $request){

        Cart::checkCartData();
        $data['selectedTotal'] = Cart::totalPrice('selected');
        $hasCart = Cart::selectedCartCollection();
        $shipping = 0;
        $tax = 0;
        $cartService = new AddToCartService();

        $code = $request->code; // techDecrypt($request->code);
        $purchaseData = PaymentLog::where('code', $code)->orderBy('id', 'desc')->first();


        $refCode = substr(sha1(time()), 29,40);
        $memberId = Auth::user()->member_id;
        $amount = $purchaseData->total;
        $orderDate = $purchaseData->order_date;
        $savingsApiResponse = $this->postPaymentNotification($refCode, $memberId, $amount, $orderDate, 1);
        $loanApiResponse = $this->postPaymentNotification($refCode, $memberId, $amount, $orderDate, 2);
        $savingsCollection = null;
        if($savingsApiResponse->getStatusCode() == 200) {
            $response_data = json_decode((string)$savingsApiResponse->getBody(), true);
            $savingsCollection = collect($response_data);
        }
        $loanCollection = null;
        if($loanApiResponse->getStatusCode() == 200) {
            $response_data = json_decode((string)$loanApiResponse->getBody(), true);
            $loanCollection = collect($response_data);
        }
        return view("gateway::coop-savings",[
            'purchaseData'=>$purchaseData,
            'savingsCollection'=>$savingsCollection,
            'loanCollection'=>$loanCollection,
        ]);

    }

    public function processCoopSavingsPayment(Request $request){
        /*
         * memberId, total, code
         */
        Cart::checkCartData();
        $data['selectedTotal'] = Cart::totalPrice('selected');
        $hasCart = Cart::selectedCartCollection();
        $shipping = 0;
        $tax = 0;
        //$cartService = new AddToCartService();
        $order = [];
        foreach($hasCart as $selected){
           $data = [
               "vendor_id"=>$selected['vendor_id'],
                "vendor_name"=> "Vendor Name 1",
                "Product_code"=> $selected['code'],
                "product_name"=>$selected['name'],
                "qty"=>$selected['quantity'],
                "Unit_Price"=>$selected['price'],
                "amount"=>"2000"
           ];
           array_push($order, $data);
        }
        $form = [
            "uid"=>1,//Auth::user()->member_id,
            "TransID"=>rand(100,1000),
            "OrderID"=>rand(10,1000),
            "TransDate"=>"2023-04-08",
            "Order"=>$order
        ];
        $extUrl = "https://www.coopeastngr.com/api/productreg.asp";
        $req = $this->sendAPIRequest($extUrl, json_encode($form));
        try {
            if($req->getStatusCode() == 200) {
                $response_data = json_decode((string)$req->getBody(), true);
                $collection = collect($response_data);
                return dd($collection);
            }
        }catch(\Exception $exception){
            return dd($exception);
        }

        /*
        return dd($order);
        $code = $request->code;
        $memberId = $request->memberId;
        $total = $request->total;
        return dd($total);*/
    }

    public function getPurchaseData($code){
        return PaymentLog::where('code', $code)->orderBy('id', 'desc')->first();
    }
}

/*
 * {"id":2,
 * "user_id":2,
 * "reference":"ORD-0002",
 * "note":null,
 * "order_date":"2022-12-17",
 * "currency_id":3,
 * "leave_door":null,
 * "other_discount_amount":"0.00000000",
 * "other_discount_type":null,
 * "shipping_charge":"0.00000000",
 * "tax_charge":"2.02400000",
 * "shipping_title":"Local Pickup",
 * "total":"42.02400000",
 * "paid":"0.00000000",
 * "total_quantity":"1.00000000",
 * "amount_received":"0.00000000",
 * "order_status_id":1,
 * "is_delivery":0,
 * "payment_status":"Unpaid",
 * "created_at":"2022-12-17T01:04:02.000000Z",
 * "updated_at":null,
 * "currency":{"id":3,"name":"USD","symbol":"$","exchange_rate":null,"exchange_from":null}
 * }
 */
