<?php

namespace App\Services;

use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;
use App\Models\Addon;
use Session;

class TwilioVideoService
{
    protected $accountSid;
    protected $apiKey;
    protected $apiSecret;

    public function __construct()
    {

        $locationID =  Session::get('selectedLocation');
        $addon = Addon::where(['location_id'=>$locationID])->first();
       

        $this->accountSid = $addon->twillio_video_accountSid ?? '';
        $this->apiKey = $addon->twillio_video_key ?? '';
        $this->apiSecret = $addon->twillio_video_secret ?? '';
    }

    public function generateToken(string $identity, string $roomName)
    {
        if(!empty($this->accountSid) && !empty($this->apiKey) && !empty($this->apiSecret)){
          $token = new AccessToken(
            $this->accountSid,
            $this->apiKey,
            $this->apiSecret,
            3600,
            $identity
        );

        $videoGrant = new VideoGrant();
        $videoGrant->setRoom($roomName);
        $token->addGrant($videoGrant);

        return $token->toJWT();
        }else{
            return false;
        }

    }
}
