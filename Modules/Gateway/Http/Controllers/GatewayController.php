<?php

namespace Modules\Gateway\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Vendor as VendorModel;
use App\Services\Product\AddToCartService;
use Carbon\Carbon;
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
use Yabacon\Paystack;

class GatewayController extends Controller
{
    use ApiResponse;

    private $helper;

    public function __construct()
    {
        $this->helper = GatewayHelper::getInstance();
        $this->cartService = new AddToCartService();
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

        /*

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
                */

        if ($purchaseData->status == 'completed') {
            $message = __('Already paid for the order.');
            return view('gateway::pay', compact('gateways', 'purchaseData', 'message'));
        }

        return view('gateway::pay', compact('gateways', 'purchaseData'));
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
            $curl = curl_init();

            //$channel = 'dnd';

            //$sender = 'N-Alert';

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                //CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>$data,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            return curl_exec($curl);

            curl_close($curl);
            /*$client = new Client();
            return  $client->request('POST', $url, [
                'json'=>$data]);*/
        }catch (\Exception $exception){
            return 'exception'.$exception;
        }
    }

    public function paymentConfirmation(Request $request)
    {
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
        $code = $request->code;
        $payment_method = $request->payment_method;
        $purchaseData = PaymentLog::where('code', $code)->orderBy('id', 'desc')->first();
        try{
            if(!empty($purchaseData)){
                $refCode = substr(sha1(time()), 29,40);
                $memberId = Auth::user()->member_id;
                $email = Auth::user()->email;
                $amount = $purchaseData->total;
                $orders = [];
                $userOrder = Order::where('reference', $code)->first();
                if(!empty($userOrder)){
                    $orderDetails = OrderDetail::where('order_id', $userOrder->id)->get();
                    if(count($orderDetails) > 0){
                        foreach($orderDetails as $detail){
                            $vendor = VendorModel::getVendorById($detail->vendor_id);
                            $product = Product::getProductById($detail->product_id);
                            $data = [
                                "vendor_id"=>$detail->vendor_id,
                                "vendor_name"=> $vendor->name ?? 'N/A',
                                "Product_code"=> $product->code ?? 'N/A',
                                "product_name"=> $product->name ?? 'N/A',
                                "qty"=>$detail->quantity ?? 0,
                                "Unit_Price"=>$detail->price ?? 0,
                                "amount"=>($detail->price * $detail->quantity)
                            ];
                            array_push($orders, $data);
                        }
                        $form = [
                            "uid"=>$memberId ?? 'TEST',
                            "TransID"=> $refCode,
                            "OrderID"=> $code, //$refCode,
                            "TransDate"=>date('Y-m-d') ?? "2023-04-08",
                            "PaymentMode"=>ucfirst($payment_method),
                            "Order"=>$orders
                        ];
                        $extUrl = "https://www.coopeastngr.com/api/productreg.asp";
                        switch ($payment_method){
                            case 'savings':
                                $savingsApiResponse = $this->postPaymentNotification($refCode, $memberId, $amount, $userOrder->order_date, 1);
                                $resp_data = json_decode((string)$savingsApiResponse->getBody(), true);
                                $savingsCollection = collect($resp_data);
                                if($savingsCollection['code'] == 0) {
                                    $form['TransID'] = $savingsCollection['TransID'];
                                    $req = $this->sendAPIRequest($extUrl, json_encode($form));
                                    try {
                                        if($req) {


                                            $this->updateOrderStatus($code, 'Paid');
                                            \App\Cart\Cart::selectedCartProductDestroy();
                                            session()->flash("success", "Congratulations! Your transaction was successful.");
                                            return redirect()->route('site.order');

                                        }else{
                                            return view("gateway::display-message",[
                                                'message'=>"Whoops! Something went wrong. Try again later",
                                                'status'=>400
                                            ]);
                                        }
                                    }catch(\Exception $exception){
                                        //return dd($exception);
                                    }
                                }else{
                                    return view("gateway::display-message",[
                                        'message'=>"Whoops! {$savingsCollection['response']}",
                                        'status'=>400
                                    ]);
                                }
                                break;
                            case 'loan':
                                $loanApiResponse = $this->postPaymentNotification($refCode, $memberId, $amount, $userOrder->order_date, 2);
                                $response_data = json_decode((string)$loanApiResponse->getBody(), true);
                                $loanCollection = collect($response_data);
                                if($loanCollection['code'] == 0) {
                                    $form['TransID'] = $loanCollection['TransID'];
                                    $req = $this->sendAPIRequest($extUrl, json_encode($form));
                                    try {
                                        if($req) {
                                            $this->updateOrderStatus($code, 'Paid');
                                            session()->flash("success", "Congratulations! Your transaction was successful.");
                                            \App\Cart\Cart::selectedCartProductDestroy();
                                            return redirect()->route('site.order');
                                        }else{
                                            return view("gateway::display-message",[
                                                'message'=>"Whoops! Something went wrong. Try again later",
                                                'status'=>400
                                            ]);
                                        }
                                    }catch(\Exception $exception){
                                        session()->flash('error', "Something went wrong. Try again later");
                                        return back();
                                    }
                                }else{
                                    return view("gateway::display-message",[
                                        'message'=>"Whoops! {$loanCollection['response']}",
                                        'status'=>400
                                    ]);
                                }
                                break;
                            case 'paystack':
                                $processedPayment = $this->chargeCard($amount, $form, $email, $code, $userOrder->order_date);
                                // $formData = $tranx->data->metadata->order;
                                //                $charge = $tranx->data->metadata->charge;
                                //                $amount = $tranx->data->amount;
                                break;
                            default:
                                session()->flash('error', "Something went wrong. Try again later");
                                return back();
                        }
                    }

                }

            }else{
                session()->flash('error', "Something went wrong. Try again later");
                return back();
            }
        }catch (\Exception $exception){
            session()->flash('error', "Something went wrong. Try again later");
            return back();
        }

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
                //return dd($collection);
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

    public function updateOrderStatus($code, $status){
        $userOrder = Order::where('reference', $code)->first();
        $userOrder->payment_status = $status;
        $userOrder->order_status_id = 4; //complete
        $userOrder->save();
    }

    public function chargeCard($amount, $data, $email, $orderCode, $orderDate){
            try{
                $paystack = new Paystack(env('PAYSTACK_SECRET_KEY'));
                $builder = new Paystack\MetadataBuilder();
                $builder->withTransaction(3);
                $builder->withOrder($data);
                $onlineCharge = ($amount * 100)/98.5;
                $onlineCharge = $onlineCharge - $amount;
                if($amount >= 2500){
                    $onlineCharge = $onlineCharge + 1.5;
                    $onlineCharge = $onlineCharge + 100;
                }
                $onlineCharge = $onlineCharge + 0.03;
                if($onlineCharge > 2000){
                    $onlineCharge = 2000;
                }
                /*$charge = $amount*0.015 + 100;
                if($charge > 2000){
                    $charge = 2000;
                }
                if($charge < 2500){
                    $charge = $amount * 0.015; //98.5
                }*/

                $builder->withCharge($onlineCharge);
                $builder->withOrderCode($orderCode);
                $builder->withOrderDate($orderDate);
                $metadata = $builder->build();
                $tranx = $paystack->transaction->initialize([
                    'amount'=>($amount+$onlineCharge)*100,       // in kobo
                    'email'=>$email,         // unique to customers
                    'reference'=>substr(sha1(time()),23,40), // unique to transactions
                    'metadata'=>$metadata
                ]);
                return redirect()->to($tranx->data->authorization_url)->send();
            }catch (Paystack\Exception\ApiException $exception){
                session()->flash("error", "Whoops! Something went wrong. Try again.");
                return back();
            }
    }

    public function processOnlinePayment(Request $request){
        $reference = isset($request->reference) ? $request->reference : '';
        if(!$reference){
            die('No reference supplied');
        }
        $paystack = new Paystack(env('PAYSTACK_SECRET_KEY'));
        try {
            $tranx = $paystack->transaction->verify([
                'reference'=>$reference, // unique to transactions
            ]);
        }catch (Paystack\Exception\ApiException $exception){
            session()->flash("error", "Whoops! Something went wrong.");
            return abort(404);
        }
        if ($tranx->data->status  === 'success') {
            try {


                $extUrl = "https://www.coopeastngr.com/api/productreg.asp";
                $refCode = substr(sha1(time()), 29,40);
                $memberId = Auth::user()->member_id;
                $order = $tranx->data->metadata->order;
                $orderCode = $tranx->data->metadata->order_code;
                $orderDate = $tranx->data->metadata->order_date;
                $charge = $tranx->data->metadata->charge;
                $amount = $tranx->data->amount;

                $form = [
                    "uid"=>$memberId ?? 'TEST',
                    "TransID"=> $refCode,
                    "OrderID"=> $orderCode, //$refCode,
                    "TransDate"=>date('Y-m-d') ?? "2023-04-08",
                    "Order"=>$order
                ];

               /* $loanApiResponse = $this->postPaymentNotification($refCode, $memberId, $amount, $orderDate, 3);
                $response_data = json_decode((string)$loanApiResponse->getBody(), true);
                $loanCollection = collect($response_data);
                if($loanCollection['code'] == 0) {*/
                    //$form['TransID'] = $refCode; // $loanCollection['TransID'];
                    $req = $this->sendAPIRequest($extUrl, json_encode($form));
                    try {
                        if($req) {
                            $this->updateOrderStatus($orderCode, 'Paid');
                            session()->flash("success", "Congratulations! Your transaction was successful.");
                            \App\Cart\Cart::selectedCartProductDestroy();
                            return redirect()->route('site.order');
                        }else{
                            return view("gateway::display-message",[
                                'message'=>"Whoops! Something went wrong. Try again later",
                                'status'=>400
                            ]);
                        }
                    }catch(\Exception $exception){
                        session()->flash('error', "Something went wrong. Try again later");
                        return back();
                    }
               /* }else{
                    return view("gateway::display-message",[
                        'message'=>"Whoops! {$loanCollection['response']}",
                        'status'=>400
                    ]);
                }*/

            }catch (Paystack\Exception\ApiException $ex){

            }

        }else{
            abort(404);
        }
    }
}

