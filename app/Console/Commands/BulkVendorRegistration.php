<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorUser;
use App\Services\Mail\UserMailService;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use League\Csv\Reader;
use Modules\Shop\Http\Models\Shop;

class BulkVendorRegistration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:vendor_registration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $reader = Reader::createFromPath(public_path('vendors.csv'), 'r');// Reader::createFromPath(public_path('vendors.csv'), 'r');
        $reader->setDelimiter(',');
        foreach ($reader->getRecords() as $key=> $row) {
            // No ean code, unicolor
            /*return dd($row[1]);
            if (empty($row[5]) || empty($row[17])) {
                continue;
            }*/
            $email = !empty($row[1]) ?  strtolower(str_replace(' ', '', ucfirst($row[1])))."@gmail.com" : "default_{$key}@gmail.com";
            $vendorData = [
                'name'=> !empty($row[2]) ? ucfirst($row[2]) : 'Contact Name',
                'email'=>$email,// 'default@gmail.com',
                'phone'=>'+234',
                'formal_name'=>!empty($row[1]) ? ucfirst($row[1]) : 'Store Name',
                'website'=>'http://www.example.com',
                'status'=>'Active',
                'sell_commissions'=>0,
                'logo'=>'logo.png',
                'alias'=>!empty($row[1]) ? ucfirst($row[1]) : 'Store Name',
                'address'=>'Default address',
                'password'=>'password123',
            ];
            $vendorMeta = [
                'description'=>!empty($row[1]) ? ucfirst($row[1]) : 'Store Name',
                'cover_photo'=>'Default',
                'vendor_logo'=>'Default',
            ];
            $this->commandVendorRegistration($vendorData, $vendorMeta);

        }
        /*
         * return [
            'name' => 'required|min:3|max:80|unique:vendors,name',
            'email' => ['required','max:99', 'unique:vendors,email', 'unique:users,email', new CheckValidEmail],
            'phone' => ['required', 'min:10', 'max:45', new CheckValidPhone],
            'formal_name' => 'max:99',
            'website' => ['nullable', 'max:191', new CheckValidURL],
            'status' => 'required|in:Pending,Active,Inactive',
            'sell_commissions' =>'nullable|numeric',
            'logo'  => ['nullable', new CheckValidFile(getFileExtensions(3))],
            'alias' => 'required|unique:shops,alias',
            'address' => 'required|max:191',
            'password' => ['required', new StrengthPassword],
        ];
         */

       /* $user = new User();
        $user->create([

        ]);*/
        //return Command::SUCCESS;
    }


    public function commandVendorRegistration($vendorData, $vendorMetaData)
    {
            $vendorRequest = new Request($vendorData);
            $vendorMetaRequest = new Request($vendorMetaData);

            $vendorData['raw_password'] = $vendorRequest->password;
            $vendorData['password'] = \Hash::make($vendorRequest->password);
            $vendorData['email'] = validateEmail($vendorRequest->email) ? strtolower($vendorRequest->email) : null;

            try {
                \DB::beginTransaction();
                $data['vendorData'] = $vendorRequest->only('name', 'email', 'phone', 'formal_name', 'website', 'status', 'sell_commissions');
                $data['vendorMetaData'] = $vendorMetaRequest->only('description', 'cover_photo', 'vendor_logo');
                $vendorId = (new Vendor)->store($data);
                $vendorRequest['vendor_id'] = $vendorId;
                (new Shop)->store($vendorRequest->only('vendor_id', 'name', 'email', 'website', 'alias', 'phone', 'address'));
                $vendorRequest['activation_code'] = NULL;
                if ($vendorRequest->status <> 'Active') {
                    $vendorRequest['activation_code'] = Str::random(10);
                }
                // Store user information
                $id = (new User)->store($vendorRequest->only('name', 'email', 'password', 'activation_code', 'status'));

                if (!empty($id)) {
                    $roleAll = Role::getAll();
                    $roles = [];

                    foreach ($vendorRequest->role_ids as $role_id) {
                        $roles[] = ['user_id' => $id, 'role_id' => $role_id];
                        $role = $roleAll->where('id', $role_id)->first();
                    }

                    if (!empty($roles)) {
                        (new RoleUser)->store($roles);
                    }

                    $vendorRequest['role'] = $role;
                    $vendorRequest['user_id'] = $id;
                    (new VendorUser)->store($vendorRequest->only('vendor_id', 'user_id', 'status'));

                    if (isset($vendorRequest->send_mail) && $vendorRequest->status != 'Inactive' && !empty($vendorRequest['email'])) {
                        $emailResponse = (new UserMailService)->send($vendorRequest);

                        if ($emailResponse['status'] == false) {
                            \DB::rollBack();
                            return redirect()->back()->withInput()->withErrors(['fail' => $emailResponse['message']]);
                        }
                    }
                }
                \DB::commit();
                echo "Success...";
                //$response = $this->messageArray(__('The :x has been successfully saved.', ['x' => __('Vendor')]), 'success');

            } catch (\Exception $e) {
                echo $e->getMessage();
                \DB::rollBack();
                $response['status'] = 'fail';
                $response['message'] = $e->getMessage();
            }

       // }
        //$this->setSessionValue($response);

        //return redirect()->route('vendors.index');
    }

//'NG1203', 'NG175', 'NG565', 'Ng390', 'NG463', 'NG1215', 'NG490', 'NG1047', 'NG1070', 'NG587', 'NG386', 'NG304', 'NG306', 'NG307', 'NG310', 'NG402', 'NG1039', 'NG439', 'NG1179', 'NG346', 'NG1005', 'NG707', 'NG1180', 'NG708', 'NG1236', 'NG564', 'NG588', 'NG870', 'NG903', 'NG589'
//46,73,74,75,76,77,82,83,97,146,148,152,169,171,172,175,182,212,215,386,466,470,623,634,645
}
