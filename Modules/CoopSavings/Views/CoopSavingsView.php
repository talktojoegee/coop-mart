<?php

/**
 * @package PaystackView
 * @author TechVillage <support@techvill.org>
 * @contributor Muhammad AR Zihad <[zihad.techvill@gmail.com]>
 * @created 14-2-22
 */

namespace Modules\CoopSavings\Views;

use Modules\Gateway\Contracts\PaymentViewInterface;
use Modules\Gateway\Services\GatewayHelper;
use Modules\CoopSavings\Entities\CoopSavings;

class CoopSavingsView implements PaymentViewInterface
{
    public static function paymentView($key)
    {
        $helper = GatewayHelper::getInstance();
        try {
            $paystack = CoopSavings::first()->data;
            return view('coopsavings::pay', [
                'instruction' => $paystack->instruction,
                'purchaseData' => $helper->getPurchaseData($key)
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => __('Purchase data not found.')]);
        }
    }
}
