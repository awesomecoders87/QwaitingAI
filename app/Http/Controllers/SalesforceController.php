<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Role;
use App\Models\Domain;
use App\Models\MessageDetail;
use App\Models\SalesforceSetting;
use App\Models\SalesforceConnection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\TenantCreated;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SalesforceController extends Controller
{




    private $teamId;
    private $locationId;
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $auth_url  = 'https://login.salesforce.com/services/oauth2/authorize';
    private $token_url = 'https://login.salesforce.com/services/oauth2/token';

    public function __construct()
    {
        // Load Salesforce settings for the logged-in tenant/location
        if (Auth::check()) {
            $this->teamId     = tenant('id');
            $this->locationId = session('selectedLocation');

            $settings = SalesforceSetting::where('team_id', $this->teamId)
                ->where('location_id', $this->locationId)
                ->first();

            if ($settings) {
                $this->client_id     = $settings->client_id;
                $this->client_secret = $settings->client_secret;

                // Ensure redirect URI has full domain
                $baseUrl = request()->getSchemeAndHttpHost();
                $this->redirect_uri = $settings->redirect_uri
                    ?: $baseUrl . '/salesforce/callback';
            }
     //https://orairaq.qwaiting.com/salesforce/callback
        }
    }
    /**
     * Step 1: Redirect user to Salesforce login
     */
    public function authorizeUser(Request $request)
    {
        $subdomain = tenant('name');
        $baseUrl   = request()->getSchemeAndHttpHost();

        $code_verifier  = $this->generateRandomString(64);
        $code_challenge = rtrim(strtr(base64_encode(hash('sha256', $code_verifier, true)), '+/', '-_'), '=');

        Session::put('code_verifier', $code_verifier);

        $state = base64_encode(json_encode([
            'subdomain' => $subdomain,
            'baseUrl'   => $baseUrl,
        ]));

        $params = [
            'response_type'         => 'code',
            'client_id'             => $this->client_id,
            'redirect_uri'          => $this->redirect_uri,
            'state'                 => $state,
            'scope'                 => 'api refresh_token offline_access',
            'code_challenge'        => $code_challenge,
            'code_challenge_method' => 'S256',
        ];

        return redirect($this->auth_url . '?' . http_build_query($params));
    }

    /**
     * Step 2: Callback from Salesforce
     */
    public function callback(Request $request)
    {

        $authorization_code = $request->query('code');
        $state  = json_decode(base64_decode($request->query('state')), true);

        if (!$authorization_code) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Missing Authorization Code!',
            ]);
        }

        $tokenResponse = $this->getAccessToken($authorization_code);

        if ($tokenResponse['status'] == 'success') {
            $accessToken  = $tokenResponse['data']['access_token'];
            $refreshToken = $tokenResponse['data']['refresh_token'];
            $instanceUrl  = $tokenResponse['data']['instance_url'];

            // Store tokens in user table
            if (Auth::check()) {
                SalesforceConnection::updateOrCreate(
                [
                    'team_id' => $this->teamId,
                    'location_id' => $this->locationId,
                ],
                [
                    'user_id' => Auth::id(), // condition (find by user_id)
                    'salesforce_token'         => $accessToken,
                    'salesforce_refresh_token' => $refreshToken,
                    'salesforce_instance_url'  => $instanceUrl,
                    'status'                   => 1,
                ]
            );
            }

            // $userList = $this->getUserList($accessToken, $instanceUrl);

            // return response()->json([
            //     'tokens' => $tokenResponse['data'],
            //     'id'=>Auth::id(),
            //     // 'users'  => $userList
            // ]);
            return redirect('/sales-force-setting')
            ->with('success', 'Salesforce connected successfully!');
        }

        // return response()->json($tokenResponse);

        return redirect('/sales-force-setting')
        ->with('error', $tokenResponse['message'] ?? 'Something went wrong!');
    }

    /**
     * Step 3: Exchange code for access token (first time login)
     */
    private function getAccessToken($authorization_code)
    {
        $code_verifier = Session::get('code_verifier');

        $response = Http::asForm()->post($this->token_url, [
            'grant_type'    => 'authorization_code',
            'code'          => $authorization_code,
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri'  => $this->redirect_uri,
            'code_verifier' => $code_verifier,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['access_token'], $data['refresh_token'], $data['instance_url'])) {
                return [
                    'status' => 'success',
                    'data'   => $data
                ];
            }
        }

        return [
            'status'       => 'error',
            'message'      => 'Unable to retrieve tokens',
            'raw_response' => $response->json()
        ];
    }

    /**
     * Step 4: Refresh access token using stored refresh token
     */
    public function getAccessTokenViaRefreshToken($refresh_token)
    {
        $response = Http::asForm()->post($this->token_url, [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refresh_token,
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['access_token'])) {

            // Store tokens in user table

                return [
                    'status'       => 'success',
                    'access_token' => $data['access_token'],
                    'instance_url' => $data['instance_url']
                ];
            }
        }

        return [
            'status'  => 'error',
            'message' => 'Unable to refresh access token',
            'raw'     => $response->json()
        ];
    }

    /**
     * Step 5: Get Salesforce user list
     */
    // public function getUserList()
    // {

    //     if(!empty(Auth::user()->salesforce_refresh_token)){
    //         $salesforce_refresh_token = Auth::user()->salesforce_refresh_token;
    //          $data =  $this->getAccessTokenViaRefreshToken($salesforce_refresh_token);
    //         if ($data['status'] == 'success') {
    //         $access_token = $data['access_token'];
    //         $instance_url = $data['instance_url'];
    //        }
    //     }

    //     if(empty($access_token)){
    //         return [
    //         'status'  => 'error',
    //         'message' => 'Access token missing',
    //     ];
    //     }
    //     if(empty($instance_url)){
    //         return [
    //         'status'  => 'error',
    //         'message' => 'Instance url missing',
    //     ];
    //     }

    //     $query = "SELECT Id, Name, Username, Email FROM User";
    //     $url   = $instance_url . "/services/data/v58.0/query";

    //     $response = Http::withHeaders([
    //         'Authorization' => 'Bearer ' . $access_token,
    //         'Content-Type'  => 'application/json'
    //     ])->get($url, ['q' => $query]);

    //     if ($response->successful()) {
    //         return [
    //             'status' => 'success',
    //             'data'   => $response->json()
    //         ];
    //     }

    //     return [
    //         'status'  => 'error',
    //         'message' => 'Failed to fetch users',
    //         'raw'     => $response->json()
    //     ];
    // }


    public function getUserList()
{
    $connectionData = SalesforceConnection::where('team_id',$this->teamId)
    ->where('location_id',$this->locationId)
    ->where('user_id',Auth::user()->id)
    ->where('status',1)
    ->first();

    if (!empty($connectionData)) {
        $salesforce_refresh_token = $connectionData->salesforce_refresh_token;
        $data = $this->getAccessTokenViaRefreshToken($salesforce_refresh_token);
        if ($data['status'] == 'success') {
            $access_token = $data['access_token'];
            $instance_url = $data['instance_url'];
        }
    }

    if (empty($access_token)) {
        return [
            'status'  => 'error',
            'message' => 'Access token missing',
        ];
    }
    if (empty($instance_url)) {
        return [
            'status'  => 'error',
            'message' => 'Instance url missing',
        ];
    }

    $query = "SELECT Id, Name, Username, Email FROM User";
    $url   = $instance_url . "/services/data/v58.0/query";

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $access_token,
        'Content-Type'  => 'application/json'
    ])->get($url, ['q' => $query]);
    if ($response->successful()) {
        $data = $response->json();

        if (!empty($data['records'])) {
            foreach ($data['records'] as $record) {
                // Only create if email does not exist
                if (!User::where('email', $record['Email'])->exists()) {

                     $baseUsername = $record['Username'];
                    $username = $baseUsername;
                    $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter++;
        }

                   $user =  User::create([
                        'name'              => $record['Name'],
                        'username'          => $username,
                        'email'             => $record['Email'],
                        'phone'             => null, // Not in Salesforce response
                        'is_admin'          => 1,
                        'email_verified_at' => now(),
                        'password'          => Hash::make('Password@123'),
                        'remember_token'    => Str::random(60),
                        'address'           => 'Imported from Salesforce',
                        'timezone'          => 'Asia/Kolkata',
                        'language'          => 'eng',
                        'country'           => '92',
                        'locations'         => [],
                        'sms_reminder_queue'=> 1,
                        'team_id'           => Auth::user()->team_id ?? tenant('id'),
                        'date_format'       => 'Y-m-d',
                        'time_format'       => 'H:i',
                        'created_at'        => now(),
                        'role_id'           => 1,
                        'updated_at'        => now()->addDays(3),
                        'is_login'          => 1,
                        'is_active'         => 1,
                        'saleforce_user_id' =>$record['Id'] ?? null,
                    ]);
    // Assign Role
        if ($adminRole = Role::where('name', 'Admin')->first()) {
            $user->roles()->attach($adminRole->id);
        }
Log::info('user :' . $user);
           // Send Email
         try {

        // Check if domain already exists
             $domainName=  Domain::where('team_id', $user->team_id)->value('domain');
            $data = [
                'domain'=>$domainName,
                'user'=> $user,
                'tenant_id' => $user->team_id,
                'admin_user_id' => $user->id,
                'username' => $username,
                'base_url' => ''
            ];

            Mail::to($user['email'])->send(new TenantCreated(
                                        ucfirst($user->name),
                                        $domainName,
                                        $username,
                                        $user['email'],
                                        'Password@123'
                                    ));

                                // Success log
                                $logData = [
                                    'team_id' =>$user->team_id,
                                    'location_id' => null,
                                    'user_id' => $user->id,
                                    'email' => $user->email ?? '',
                                    'contact' => $user->phone ?? '',
                                    'type' => MessageDetail::AUTOMATIC_TYPE,
                                    'event_name' => 'create a salesforce user',
                                    'channel' => 'email',
                                    'status' => 'sent',
                                    'response_status' => json_encode($data),
                                    'failed_reason' => null
                                ];
                            } catch (\Exception $e) {
                                // Failed log
                                $logData = [
                                     'team_id' =>$user->team_id,
                                   'location_id' => null,
                                    'user_id' => $user->id,
                                    'email' => $user->email ?? '',
                                    'contact' => $user->phone ?? '',
                                    'type' => MessageDetail::AUTOMATIC_TYPE,
                                    'event_name' => 'create a salesforce user',
                                    'channel' => 'email',
                                    'status' => 'failed',
                                    'response_status' => json_encode($data),
                                    'failed_reason' => $e->getMessage()
                                ];
                            }

        MessageDetail::storeLog($logData);
                }else{
                    User::where('email', $record['Email'])->update([ 'saleforce_user_id' =>$record['Id'] ?? null]);
                }
            }
        }

        // return [
        //     'status' => 'success',
        //     'data'   => $data
        // ];

        return redirect()
            ->route('tenant.sales-force-setting')
            ->with('success', 'Salesforce users imported successfully.');
    }

     return redirect()
        ->route('tenant.sales-force-setting')
        ->with('error', 'Failed to fetch users from Salesforce.');
    // return [
    //     'status'  => 'error',
    //     'message' => 'Failed to fetch users',
    //     'raw'     => $response->json()
    // ];
}

    /**
     * Helper: Random string generator
     */
    private function generateRandomString($length = 64)
    {
        return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / 62))), 0, $length);
    }
}
