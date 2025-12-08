<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SalesforceService
{
    protected $accessToken;
    protected $instanceUrl;
    protected $clientId;
    protected $clientSecret;
    protected $tokenUrl;

    public function __construct($clientId = null, $clientSecret = null, $tokenUrl = null)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->tokenUrl = $tokenUrl ?: 'https://login.salesforce.com/services/oauth2/token';
    }

    public function authenticate()
    {
        $response = Http::asForm()->post('https://login.salesforce.com/services/oauth2/token', [
            'grant_type' => 'password',
            'client_id' => config('services.salesforce.client_id'),
            'client_secret' => config('services.salesforce.client_secret'),
            'username' => config('services.salesforce.username'),
            'password' => config('services.salesforce.password'),
        ]);

        if ($response->failed()) {
            throw new \Exception('Salesforce OAuth failed: ' . $response->body());
        }

        $data = $response->json();

        $this->accessToken = $data['access_token'];
        $this->instanceUrl = $data['instance_url'];

        return $this;
    }

    public function getUsers()
    {
        if (!$this->accessToken || !$this->instanceUrl) {
            $this->authenticate();
        }

        $query = "SELECT Id, Name, Email FROM User LIMIT 100";

        $response = Http::withToken($this->accessToken)
            ->get($this->instanceUrl . "/services/data/v57.0/query", [
                'q' => $query,
            ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch Salesforce users: ' . $response->body());
        }

        return $response->json()['records'];
    }

    /**
     * Refresh access token using stored refresh token
     */
    public function getAccessTokenViaRefreshToken($refresh_token)
    {
        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refresh_token,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['access_token'])) {
                return [
                    'status'       => 'success',
                    'access_token' => $data['access_token'],
                    'instance_url' => $data['instance_url']
                ];
            }
        }

        return [
            'status' => 'error',
            'message' => 'Unable to refresh access token',
            'raw'     => $response->json()
        ];
    }

    /**
     * Update an existing Lead in Salesforce using a refresh token
     */
    public function updateLead(string $refreshToken, string $leadId, array $fields)
    {
        try {
            $data = $this->getAccessTokenViaRefreshToken($refreshToken);
            $access_token = $data['access_token'] ?? '';
            $instance_url = $data['instance_url'] ?? '';


            if (empty($access_token) || empty($instance_url)) {
                return [
                    'status' => 'error',
                    'message' => 'Unable to refresh access token',
                    'response' => $data
                ];
            }

            $url = $instance_url . '/services/data/v63.0/sobjects/Lead/'.$leadId;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
                 'Accept'        => 'application/json',
            ])->patch($url, $fields);

            if ($response->successful() || $response->status() === 204) {
                return [
                    'status' => 'success',
                    'message' => 'Lead updated successfully',
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Failed to update lead',
                 'http_status' => $response->status(),
                'response_json' => $response->json(),
                'response_body' => $response->body(),
            ];
        } catch (\Exception $e) {

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a lead in Salesforce
     */
    public function createLead(array $salesForceData)
    {
        try {
            // Refresh token call
            $refreshToken = $salesForceData['refresh_token'];
            $data = $this->getAccessTokenViaRefreshToken($refreshToken);
            $access_token = $data['access_token'] ?? '';
            $instance_url = $data['instance_url'] ?? '';

            if (!isset($access_token) || empty($access_token)) {
                return [
                    'status' => 'error',
                    'message' => 'Unable to refresh access token',
                    'response' => $data
                ];
            }

            // Salesforce API endpoint for Lead
            $url = $instance_url . '/services/data/v63.0/sobjects/Lead/';

            // Prepare payload
            $payload = [
                'LastName'              => !empty($salesForceData['FirstName']) ? $salesForceData['FirstName'] : 'Guest',
                'MobilePhone'           => $salesForceData['Phone'] ?? null,
                'QwaitingSyncDate__c'   => $salesForceData['Qwaiting_Sync_Date__c'] ?? now()->toDateTimeString(),
                'ServiceName__c'        => $salesForceData['Service_Name__c'] ?? null,
                'Token__c'              => $salesForceData['Token'] ?? null,
                'Page__c'               => $salesForceData['Page'] ?? null,
                'Created__c'            => $salesForceData['Created'] ?? null,
                'QueueStorageId__c'     => (int)$salesForceData['queue_storage_id'] ?? null,
                'Company'               => $salesForceData['Company'] ?? '',
                'Mobile_2__c'           => $salesForceData['Mobile'] ?? '',
                'Age__c'                => $salesForceData['Age'] ?? '',
                'Job_Title__c'          => $salesForceData['Occupation'] ?? '',
                'City'                  => $salesForceData['Address'] ?? '',
                'Marital_Status__c'     => $salesForceData['Marital'] ?? '',
                'Previous_Contact__c'   => $salesForceData['Previous'] ?? '',
                'Purpose_of_Visit__c'   => $salesForceData['Purpose'] ?? '',
                'Unit_Type__c'          => $salesForceData['Unit'] ?? '',
                'Description'           => $salesForceData['Note'] ?? '',
                'Ownerid'               => $salesForceData['AssignId'] ?? '005Hu00000SBZ8bIAH',
                'LeadSource'            => "Walk-in",
            ];

            if (empty($salesForceData['Email'])) {
                $payload['Email_Not_Available__c'] = true;
            } else {
                $payload['Email'] = $salesForceData['Email'];
            }

            // Make request using Laravel HTTP client
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ])->post($url, $payload);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['id'])) {
                return [
                    'status' => 'success',
                    'message' => "Lead created successfully. Salesforce ID: " . $responseData['id'],
                    'id' => $responseData['id'],
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Failed to create lead',
                    'response' => $responseData
                ];
            }

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
