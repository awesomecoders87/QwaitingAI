<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Twilio\Rest\Client;
use App\Services\SMS\SmsService;
use App\Services\WhatsApp\WhatsappService;
use Illuminate\Support\Facades\Session;
use App\Services\WhatsApp\InteraktService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SmsAPI extends Model
{
    use HasFactory;
    protected $table  = 'sms_api';
    protected $fillable = ['team_id', 'location_id', 'contact', 'message', 'json', 'sms_api_url', 'type', 'is_sms', 'is_whatsapp', 'is_template', 'url_method', 'status', 'authentication', 'token', 'created_at', 'updated_at'];
    const TYPE_WHATSAPP = 'WHATSAPP';
    const TYPE_MOBILE = 'MOBILE';
    const TICKET_TEMPLATE_ID = '1207166313155256626';
    protected $casts = [
        'json' => 'array',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public static function viewDetails($teamId, $type = null, $location = null)
    {
        $location = $location ?? Session::get('selectedLocation');

        return self::where('team_id', $teamId)
            ->when($location, function ($query) use ($location) {
                $query->where('location_id', $location);
            })
            ->where('status', 1)
            ->first();
    }
     public static function sendSms($teamId, $data, $title, $type = null, $logData = [])
    {

        Log::info("data: " . json_encode($data));
        $gettype = $type ?? $logData['type'] ?? 'queue';
        $locationId = $data['location_id'] ??  $data['locations_id'] ?? Session::get('selectedLocation');

        Session::put('selectedLocationcron', $locationId);
        Session::put('TeamIDcron', $teamId);

        $smsRecords = self::where('team_id', $teamId)
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->where('status', 1)
            ->get();

        Log::info("smsRecords: " . json_encode($smsRecords));
        if ($smsRecords->isEmpty()) {
            return;
        }

        foreach ($smsRecords as $smsRecord) {
            $template = '';
            $templateName = '';

            // Fetch correct template model
            $getTemplate = $smsRecord->is_sms == 1
                ? MessageTemplate::where('team_id', $teamId)->where('location_id', $locationId)->first()
                : WhatsappTemplate::where('team_id', $teamId)->where('location_id', $locationId)->first();
Log::info("getTemplate: " . json_encode($getTemplate));
            if ($getTemplate) {
                if ($title == 'ticket created' && $getTemplate->ticket_generation_message_status == SmtpDetails::ENABLE) {
                    $template = $getTemplate->ticket_generation_message ?: $title;
                    $templateName = $getTemplate->ticket_generation_message_template;
                } elseif ($title == 'booking confirmed' && $getTemplate->new_booking_sms_message_status == SmtpDetails::ENABLE && $smsRecord->is_sms == 1) {
                    $template = $getTemplate->new_booking_sms_message ?? $title;
                    $templateName = $getTemplate->new_booking_sms_message_template;
                    Log::info("template 1: ");
                }  elseif ($title == 'booking confirmed' && $getTemplate->new_booking_sms_status == SmtpDetails::ENABLE && $smsRecord->is_sms != 1) {
                    $template = $getTemplate->new_booking_sms ?: $title;
                    $templateName = $getTemplate->new_booking_sms_template;
                    Log::info("template 1 whstaspp: ");
                } elseif ($title == 'call' && $getTemplate->next_call_message_status == SmtpDetails::ENABLE) {
                    $template = $getTemplate->next_call_message ?: $title;
                    $templateName = $getTemplate->next_call_message_template;
                } elseif ($title == 'rating survey' && $getTemplate->feedback_sms_message_status == SmtpDetails::ENABLE) {
                    $template = $getTemplate->feedback_sms_message ?: $title;
                    $templateName = $getTemplate->feedback_sms_message_template;
                }
                elseif ($title == 'call skip' && $getTemplate->skip_call_message_status == SmtpDetails::ENABLE) {
                    $template = $getTemplate->skip_call_message ?: $title;
                    $templateName = $getTemplate->skip_call_message_template;
                }
                elseif ($title == 'recall' && $getTemplate->recall_message_status == SmtpDetails::ENABLE) {
                    $template = $getTemplate->recall_message ?: $title;
                    $templateName = $getTemplate->recall_message_template;
                }
                elseif ($title == 'booking rescheduled' && $getTemplate->reschedule_booking_sms_status == SmtpDetails::ENABLE) {
                    $template = $getTemplate->reschedule_booking_sms ?: $title;
                    $templateName = $getTemplate->reschedule_booking_sms_template;
                } elseif ($title == 'booking cancelled' && $getTemplate->cancel_booking_sms_status == SmtpDetails::ENABLE) {
                    $template = $getTemplate->cancel_booking_sms ?: $title;
                    $templateName = $getTemplate->cancel_booking_sms_template;
                } elseif ($title == 'reminder' && $getTemplate->reminder_message_status == SmtpDetails::ENABLE) {
                    $template = $getTemplate->reminder_message ?: $title;
                    $templateName = $getTemplate->reminder_message_template;
                } elseif ($title == 'payment_success' && $getTemplate->stripe_sms_message_status == SmtpDetails::ENABLE) {
                    $template = $getTemplate->stripe_sms_message ?: $title;
                    $templateName = $getTemplate->stripe_sms_message_template;
                }
            }
Log::info("template title: " . $title);
Log::info("template first sms: " . $template);
Log::info("templateName first sms: " . $templateName);

            $receiverNumber = $data['phone_code'] . $data['phone'];

            //   Log::info("receiverNumber: ".$receiverNumber);
            //   Log::info("templateName: ".$templateName);


            // Skip if no valid template
            if (empty($template) && !empty($title)) {
                $template = $title;
            }

            $finalTeamId = $teamId ?? tenant('id');
            $panelName = ucfirst(tenant('name'));

            // Replace placeholders
            $formattedMessage = SmtpDetails::replaceTemplatePlaceholders($template, $data, $finalTeamId);
            if($title == 'reminder'){
                $message_type =MessageDetail::AUTOMATIC_TYPE;
            }else{
                $message_type =MessageDetail::TRIGGERED_TYPE;

            }

            try {
                // Send via appropriate channel
                if ($smsRecord->is_sms == 1) {
                    // Log before sending

                    $templateId='';
                    $smsService = new SmsService();
                    Log::info("template: " . json_encode($template));
                    Log::info("templateName: " . json_encode($templateName));
                    Log::info("is_template: " . $smsRecord->is_template);
                    if($getTemplate->enable_template_name == 1 && !empty($template) && !empty($templateName) &&  $smsRecord->is_template == 1){

                        $templateId = $templateName;
                        Log::info("templateId: " .$templateName);
                    }

                    // $response = $smsService->sendSms($receiverNumber, $formattedMessage, $finalTeamId,null,[],$templateId);
                    
                    $logData = [
                        'team_id' => $finalTeamId,
                        'location_id' => $locationId,
                        'user_id' => $logData['user_id'] ?? null,
                        'customer_id' => $logData['customer_id'] ?? null,
                        'booking_id' => $logData['booking_id'] ?? null,
                        'queue_id' =>  $logData['queue_id'] ?? null,
                        'queue_storage_id' =>  $logData['queue_storage_id'] ?? null,
                        'contact' => $receiverNumber,
                        'type' => $message_type,
                        'event_name' => $logdata['event_name'] ?? null,
                        'message' => $formattedMessage,
                        'status' => 'pending', // Will be updated to 'sent' after successful send
                        'response_status' => null,
                        'segment' => self::calculateSmsSegments($formattedMessage),
                    ];

                    $messageDetail = MessageDetail::create($logData);
                    
                    // Now send SMS with MessageDetail ID
                    $response = $smsService->sendSms($receiverNumber, $formattedMessage, $finalTeamId, null, [], $templateId, $messageDetail->id);

                    // Log::info("response sms: " . json_encode($response));
                    if ($messageDetail) {
                        $messageDetail->update([
                            'status' => 'sent',
                            'response_status' => json_encode($response)
                        ]);
                    }
                }

                if ($smsRecord->is_whatsapp == 1) {

                     if ($getTemplate->enable_template_name == 1) {
                //   Log::info("enable_template_name: ".$getTemplate->enable_template_name);
                //    Log::info("template: ".$template);
                if (!empty($template) && !empty($templateName) &&  $smsRecord->is_template == 1 && $smsRecord->sms_api_url == 'https://api.interakt.ai/v1/public/message/') {
                    // Step 1: Extract placeholders like {{token}}, {{panel_name}} etc.
                    preg_match_all('/{{.*?}}/', $template, $matches);
                    $placeholders = $matches[0]; // Includes the curly braces
                    Log::info("placeholders: " . json_encode($placeholders));
                    // Step 2: Replace placeholders using helper method
                    $bodyValues = self::replaceTemplateVariables($placeholders, $data, $teamId);
                    $url = 'https://api.interakt.ai/v1/public/message/';

                    $token = $smsRecord->token;

                    if (empty($token)) {
                        return response()->json(['error' => 'Token is required'], 422);
                    }

                    // Step 3: Inject required keys to $data
                    $logData['fullPhoneNumber'] = $receiverNumber;
                    $logData['bodyValues'] = $bodyValues;
                    $logData['template'] = $templateName;
                    $logData['url'] = $url;
                    $logData['token'] = $token;

                    Log::info("logData: " . json_encode($logData));
                    // Step 4: Call Interakt send method

                    self::sendInteraktMessage($logData);


                }

                  if (!empty($template) && !empty($templateName) &&  $smsRecord->is_template == 1 && $smsRecord->sms_api_url == 'https://api.gupshup.io/wa/api/v1/template/msg') {
                    // Step 1: Extract placeholders like {{token}}, {{panel_name}} etc.
                    preg_match_all('/{{.*?}}/', $template, $matches);
                    $placeholders = $matches[0]; // Includes the curly braces
                    Log::info("placeholders: " . json_encode($placeholders));
                    // Step 2: Replace placeholders using helper method
                    $bodyValues = self::replaceTemplateVariables($placeholders, $data, $teamId);
                    $url = 'https://api.gupshup.io/wa/api/v1/template/msg';

                    $token = $smsRecord->token;
                    $gupshupdata = json_decode($smsRecord->json, true);

                    $credentials = collect($gupshupdata)->pluck('parameter_value', 'parameter_key')->toArray();

                    if (empty($credentials['GUPSHUP_API_KEY'])) {
                         Log::info("API is required");
                        return response()->json(['error' => 'API is required'], 422);
                    }
                    if (empty($credentials['GUPSHUP_SOURCE'])) {
                          Log::info("GUPSHUP SOURCE is required");
                        return response()->json(['error' => 'GUPSHUP SOURCE is required'], 422);
                    }
                    if (empty($credentials['GUPSHUP_SRC_NAME'])) {
                          Log::info("GUPSHUP SRC NAME is required");
                        return response()->json(['error' => 'GUPSHUP SRC NAME is required'], 422);
                    }

                    $payload= [$receiverNumber, // destination phone
                    $templateName,
                    $bodyValues,
                    $credentials['GUPSHUP_API_KEY'],
                    $credentials['GUPSHUP_SOURCE'],
                    $credentials['GUPSHUP_SRC_NAME']];
 Log::info("payload: " . json_encode( $payload));

               try {
    // Send template message
    $response = self::sendTemplateMessage(
        $receiverNumber, // destination phone
        $templateName,
        $bodyValues,
        $credentials['GUPSHUP_API_KEY'],
        $credentials['GUPSHUP_SOURCE'],
        $credentials['GUPSHUP_SRC_NAME']
    );

    // Check Gupshup response status
    if (!empty($response) && isset($response['status']) && $response['status'] === 'submitted') {
        // Success log
        $logData = [
            'team_id'          => $finalTeamId,
            'location_id'      => $locationId,
            'user_id'          => $logData['user_id'] ?? null,
            'customer_id'      => $logData['customer_id'] ?? null,
            'queue_id'         => $logData['queue_id'] ?? null,
              'booking_id' => $logData['booking_id'] ?? null,
            'queue_storage_id' => $logData['queue_storage_id'] ?? null,
            'contact'          => $receiverNumber,
            'type'             => $message_type,
            'event_name'       => $logData['event_name'] ?? null,
            'channel'          => 'whatsapp',
            'message'          => $formattedMessage,
            'segment'          => self::calculateSmsSegments($formattedMessage),
            'status'           => 'sent',
        ];
    } else {
        // Error log
        $logData = [
            'team_id'          => $finalTeamId,
            'location_id'      => $locationId,
            'user_id'          => $logData['user_id'] ?? null,
            'customer_id'      => $logData['customer_id'] ?? null,
            'queue_id'         => $logData['queue_id'] ?? null,
            'queue_storage_id' => $logData['queue_storage_id'] ?? null,
              'booking_id' => $logData['booking_id'] ?? null,
            'contact'          => $receiverNumber,
            'type'             => $message_type,
            'event_name'       => $logData['event_name'] ?? null,
            'channel'          => 'whatsapp',
            'failed_reason'    => $response['message'] ?? 'Unknown error',
            'status'           => 'failed',
        ];
    }

    MessageDetail::create($logData);

} catch (\Exception $e) {
    // Exception log
    $logData = [
        'team_id'          => $finalTeamId,
        'location_id'      => $locationId,
        'user_id'          => $logData['user_id'] ?? null,
        'customer_id'      => $logData['customer_id'] ?? null,
        'queue_id'         => $logData['queue_id'] ?? null,
        'queue_storage_id' => $logData['queue_storage_id'] ?? null,
          'booking_id' => $logData['booking_id'] ?? null,
        'contact'          => $receiverNumber,
        'type'             => $message_type,
        'event_name'       => $logData['event_name'] ?? null,
        'channel'          => 'whatsapp',
        'failed_reason'    => $e->getMessage(),
        'status'           => 'failed',
    ];

    MessageDetail::create($logData);
}
                }

            }else{
                    //   SmsReport::create([
                    //     'team_id'     => $finalTeamId,
                    //     'location_id' => $locationId,
                    //     'message'     => $formattedMessage,
                    //     'contact'     => $receiverNumber,
                    //     'status'      => 'sent',
                    //     'channel'     => 'whatsapp',
                    // ]);

                    $whatsappService = new WhatsappService();
                    $response = $whatsappService->sendWhatsappSms($receiverNumber, $formattedMessage, $finalTeamId, $gettype);

                    $logData = [
                        'team_id' => $finalTeamId,
                        'location_id' => $locationId,
                        'user_id' => $logData['user_id'] ?? null,
                        'customer_id' => $logData['customer_id'] ?? null,
                        'queue_id' =>  $logData['queue_id'] ?? null,
                        'queue_storage_id' =>  $logData['queue_storage_id'] ?? null,
                        'contact' => $receiverNumber,
                        'type' => $message_type,
                          'booking_id' => $logData['booking_id'] ?? null,
                        'event_name' => $logdata['event_name'] ?? null,
                        'channel' => 'whatsapp',
                        'message' => $formattedMessage,
                        'segment' => self::calculateSmsSegments($formattedMessage)
                    ];

                    MessageDetail::create($logData);
            }

                }
            } catch (\Throwable $e) {
                Log::error("Team ID: {$finalTeamId} | Send error: " . $e->getMessage());

                // // Log failure in SmsReport
                // SmsReport::create([
                //     'team_id'       => $finalTeamId,
                //     'location_id'   => $locationId,
                //     'message'       => $formattedMessage ?? '',
                //     'contact'       => $receiverNumber ?? '',
                //     'status'        => 'failed',
                //     'type'        =>  $gettype,
                //     'channel'       => $smsRecord->is_sms == 1 ? 'sms' : 'whatsapp',
                //     'failed_reason' => $e->getMessage(),
                // ]);

                $logData = [
                    'team_id' => $finalTeamId,
                    'location_id' => $locationId,
                    'user_id' => $logData['user_id'] ?? null,
                    'customer_id' => $logData['customer_id'] ?? null,
                    'queue_id' =>  $logData['queue_id'] ?? null,
                    'queue_storage_id' =>  $logData['queue_storage_id'] ?? null,
                    'contact' => $receiverNumber,
                      'booking_id' => $logData['booking_id'] ?? null,
                    'type' => $message_type,
                    'event_name' => $logdata['event_name'] ?? null,
                    'channel' => $smsRecord->is_sms == 1 ? 'sms' : 'whatsapp',
                    'failed_reason' => $e->getMessage(),
                ];

                MessageDetail::create($logData);

                return 'Error: ' . $e->getMessage();
            }
        }

        return $response ?? true;
    }

    // public static function sendSms($teamId, $data, $title, $type = null, $logData = [])
    // {
    //     Log::info("data: " . json_encode($data));
    //     $gettype = $type ?? $logData['type'] ?? 'queue';
    //     $locationId = $data['location_id'] ?? Session::get('selectedLocation');
    //     $smsRecords = self::where('team_id', $teamId)
    //         ->when($locationId, fn($q) => $q->where('location_id', $locationId))
    //         ->where('status', 1)
    //         ->get();

    //     Log::info("smsRecords: " . json_encode($smsRecords));
    //     if ($smsRecords->isEmpty()) {
    //         return;
    //     }

    //     foreach ($smsRecords as $smsRecord) {
    //         $template = '';
    //         $templateName = '';

    //         // Fetch correct template model
    //         $getTemplate = $smsRecord->is_sms == 1
    //             ? MessageTemplate::where('team_id', $teamId)->where('location_id', $locationId)->first()
    //             : WhatsappTemplate::where('team_id', $teamId)->where('location_id', $locationId)->first();


    //         if ($getTemplate) {
    //             if ($title == 'ticket created' && $getTemplate->ticket_generation_message_status == SmtpDetails::ENABLE) {
    //                 $template = $getTemplate->ticket_generation_message ?: $title;
    //                 $templateName = $getTemplate->ticket_generation_message_template;
    //             } elseif ($title == 'booking confirmed' && $getTemplate->new_booking_sms_status == SmtpDetails::ENABLE) {
    //                 $template = $getTemplate->new_booking_sms ?: $title;
    //                 $templateName = $getTemplate->new_booking_sms_message_template;
    //             } elseif ($title == 'call' && $getTemplate->next_call_message_status == SmtpDetails::ENABLE) {
    //                 $template = $getTemplate->next_call_message ?: $title;
    //                 $templateName = $getTemplate->next_call_message_template;
    //             } elseif ($title == 'rating survey' && $getTemplate->feedback_sms_message_status == SmtpDetails::ENABLE) {
    //                 $template = $getTemplate->feedback_sms_message ?: $title;
    //                 $templateName = $getTemplate->feedback_sms_message_template;
    //             }
    //             elseif ($title == 'call skip' && $getTemplate->skip_call_message_status == SmtpDetails::ENABLE) {
    //                 $template = $getTemplate->skip_call_message ?: $title;
    //                 $templateName = $getTemplate->skip_call_message_template;
    //             }
    //             elseif ($title == 'recall' && $getTemplate->recall_message_status == SmtpDetails::ENABLE) {
    //                 $template = $getTemplate->recall_message ?: $title;
    //                 $templateName = $getTemplate->recall_message_template;
    //             }
    //             elseif ($title == 'booking rescheduled' && $getTemplate->reschedule_booking_sms_status == SmtpDetails::ENABLE) {
    //                 $template = $getTemplate->reschedule_booking_sms ?: $title;
    //                 $templateName = $getTemplate->reschedule_booking_sms_template;
    //             } elseif ($title == 'booking cancelled' && $getTemplate->cancel_booking_sms_status == SmtpDetails::ENABLE) {
    //                 $template = $getTemplate->cancel_booking_sms ?: $title;
    //                 $templateName = $getTemplate->cancel_booking_sms_template;
    //             } elseif ($title == 'reminder' && $getTemplate->reminder_message_status == SmtpDetails::ENABLE) {
    //                 $template = $getTemplate->reminder_message ?: $title;
    //                 $templateName = $getTemplate->reminder_message_template;
    //             }
    //         }

    //         $receiverNumber = $data['phone_code'] . $data['phone'];

    //         //   Log::info("receiverNumber: ".$receiverNumber);
    //         //   Log::info("templateName: ".$templateName);
    //         if ($getTemplate->enable_template_name == 1) {
    //             //   Log::info("enable_template_name: ".$getTemplate->enable_template_name);
    //             //    Log::info("template: ".$template);
    //             if (!empty($template) && !empty($templateName) &&  $smsRecord->is_template == 1 && $smsRecord->sms_api_url == 'https://api.interakt.ai/v1/public/message/') {
    //                 // Step 1: Extract placeholders like {{token}}, {{panel_name}} etc.
    //                 preg_match_all('/{{.*?}}/', $template, $matches);
    //                 $placeholders = $matches[0]; // Includes the curly braces
    //                 Log::info("placeholders: " . json_encode($placeholders));
    //                 // Step 2: Replace placeholders using helper method
    //                 $bodyValues = self::replaceTemplateVariables($placeholders, $data, $teamId);
    //                 $url = 'https://api.interakt.ai/v1/public/message/';

    //                 $token = $smsRecord->token;

    //                 if (empty($token)) {
    //                     return response()->json(['error' => 'Token is required'], 422);
    //                 }

    //                 // Step 3: Inject required keys to $data
    //                 $logData['fullPhoneNumber'] = $receiverNumber;
    //                 $logData['bodyValues'] = $bodyValues;
    //                 $logData['template'] = $templateName;
    //                 $logData['url'] = $url;
    //                 $logData['token'] = $token;

    //                 Log::info("logData: " . json_encode($logData));
    //                 // Step 4: Call Interakt send method

    //                 self::sendInteraktMessage($logData);

    //                 return true;
    //             }
    //             return false;
    //         }

    //         // Skip if no valid template
    //         if (empty($template) && !empty($title)) {
    //             $template = $title;
    //         }

    //         $finalTeamId = $teamId ?? tenant('id');
    //         $panelName = ucfirst(tenant('name'));

    //         // Replace placeholders
    //         $formattedMessage = SmtpDetails::replaceTemplatePlaceholders($template, $data, $finalTeamId);


    //         try {
    //             // Send via appropriate channel
    //             if ($smsRecord->is_sms == 1) {
    //                 // Log before sending
    //                 SmsReport::create([
    //                     'team_id'     => $finalTeamId,
    //                     'location_id' => $locationId,
    //                     'message'     => $formattedMessage,
    //                     'contact'     => $receiverNumber,
    //                     'status'      => 'sent',
    //                     'channel'     => 'sms',
    //                 ]);

    //                 $smsService = new SmsService();
    //                 $response = $smsService->sendSms($receiverNumber, $formattedMessage, $finalTeamId);

    //                 $logData = [
    //                     'team_id' => $finalTeamId,
    //                     'location_id' => $locationId,
    //                     'user_id' => $logData['user_id'] ?? null,
    //                     'customer_id' => $logData['customer_id'] ?? null,
    //                     'booking_id' => $logData['booking_id'] ?? null,
    //                     'queue_id' =>  $logData['queue_id'] ?? null,
    //                     'queue_storage_id' =>  $logData['queue_storage_id'] ?? null,
    //                     'contact' => $receiverNumber,
    //                     'type' => MessageDetail::TRIGGERED_TYPE,
    //                     'event_name' => $logdata['event_name'] ?? null,
    //                     'message' => $formattedMessage,
    //                     'segment' => self::calculateSmsSegments($formattedMessage)
    //                 ];

    //                 MessageDetail::create($logData);
    //             }

    //             if ($smsRecord->is_whatsapp == 1) {
    //                 SmsReport::create([
    //                     'team_id'     => $finalTeamId,
    //                     'location_id' => $locationId,
    //                     'message'     => $formattedMessage,
    //                     'contact'     => $receiverNumber,
    //                     'status'      => 'sent',
    //                     'channel'     => 'whatsapp',
    //                 ]);

    //                 $whatsappService = new WhatsappService();
    //                 $response = $whatsappService->sendWhatsappSms($receiverNumber, $formattedMessage, $finalTeamId, $gettype);

    //                 $logData = [
    //                     'team_id' => $finalTeamId,
    //                     'location_id' => $locationId,
    //                     'user_id' => $logData['user_id'] ?? null,
    //                     'customer_id' => $logData['customer_id'] ?? null,
    //                     'queue_id' =>  $logData['queue_id'] ?? null,
    //                     'queue_storage_id' =>  $logData['queue_storage_id'] ?? null,
    //                     'contact' => $receiverNumber,
    //                     'type' => MessageDetail::TRIGGERED_TYPE,
    //                     'event_name' => $logdata['event_name'] ?? null,
    //                     'channel' => 'whatsapp',
    //                     'message' => $formattedMessage,
    //                     'segment' => self::calculateSmsSegments($formattedMessage)
    //                 ];

    //                 MessageDetail::create($logData);
    //             }
    //         } catch (\Throwable $e) {
    //             Log::error("Team ID: {$finalTeamId} | Send error: " . $e->getMessage());

    //             // Log failure in SmsReport
    //             SmsReport::create([
    //                 'team_id'       => $finalTeamId,
    //                 'location_id'   => $locationId,
    //                 'message'       => $formattedMessage ?? '',
    //                 'contact'       => $receiverNumber ?? '',
    //                 'status'        => 'failed',
    //                 'type'        =>  $gettype,
    //                 'channel'       => $smsRecord->is_sms == 1 ? 'sms' : 'whatsapp',
    //                 'failed_reason' => $e->getMessage(),
    //             ]);

    //             $logData = [
    //                 'team_id' => $finalTeamId,
    //                 'location_id' => $locationId,
    //                 'user_id' => $logData['user_id'] ?? null,
    //                 'customer_id' => $logData['customer_id'] ?? null,
    //                 'queue_id' =>  $logData['queue_id'] ?? null,
    //                 'queue_storage_id' =>  $logData['queue_storage_id'] ?? null,
    //                 'contact' => $receiverNumber,
    //                 'type' => MessageDetail::TRIGGERED_TYPE,
    //                 'event_name' => $logdata['event_name'] ?? null,
    //                 'channel' => $smsRecord->is_sms == 1 ? 'sms' : 'whatsapp',
    //                 'failed_reason' => $e->getMessage(),
    //             ];

    //             MessageDetail::create($logData);

    //             return 'Error: ' . $e->getMessage();
    //         }
    //     }

    //     return $response ?? true;
    // }

    public static function currentQueueSms($phone = null, $message = null, $teamId = null, $type = null)
    {
        $team = $teamId ?? tenant('id');
        $gettype = $type ?? 'other';
        if (empty($phone) || empty($message)) {
            Log::error("SMS Error: Missing phone or message");
            return false;
        }

        //   Log::info("send phone:".$phone);
        $smsService = new SmsService();
        return  $data =  $smsService->sendSms($phone, $message, $team, $gettype);
    }

    public static function  replacePlaceholders($message, $data)
    {
        foreach ($data as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $message = str_replace($placeholder, $value, $message);
        }
        return $message;
    }

    public static function replaceTemplateVariables(array $placeholders = [], array $data = [], $teamId = null)
    {
        if ($teamId) {
            $team = Tenant::find($teamId);
            $data['panel_name'] = $team->name ?? '';
        }

        // Define aliases or fallbacks
        $data['service_1'] = (string)($data['category_name'] ?? 'Test');
        $data['service_2'] = (string)($data['secondC_name'] ?? 'Test');
        $data['service_3'] = (string)($data['thirdC_name'] ?? 'Test');
        $data['queue_count'] = (string)($data['pending_count'] ?? '0');
        $data['booking_id'] = (string)($data['refID'] ?? ($data['booking_id'] ?? 'Test'));
        $data['customer_name'] = (string)($data['name'] ?? 'Test');
        $data['waiting_time'] = (string)($data['waiting_time'] ?? 0);
        $data['user_name'] = (string)($data['user_name'] ?? 'Test');
        $data['user_email'] = (string)($data['user_email'] ?? 'Test');
        $data['user_id'] = (string)($data['user_id'] ?? 'Test');
        $data['amount'] = (string)($data['amount'] ?? '0');
        $data['payment_id'] = (string)($data['payment_id'] ?? 'Test');
        $data['payment_date'] = (string)($data['payment_date'] ?? 'Test');
        $data['wallet_balance'] = (string)($data['wallet_balance'] ?? '0');
        $data['app_name'] = (string)($data['app_name'] ?? $data['panel_name'] ?? 'Test');

        $values = [];

        foreach ($placeholders as $placeholder) {
            preg_match('/{{(.*?)}}/', $placeholder, $match);
            $key = $match[1] ?? null;
            $values[] = $key ? trim((string)($data[$key] ?? 'Test')) : 'Test';
        }

        return $values;
    }
    // public static function sendSmss($teamId,$data,$isReminder = false)
    // {
    //     $receiverNumber = '+91'.$data['phone'];
    //     $details = self::viewDetails($teamId,self::TYPE_MOBILE);
    //     if($isReminder == false)
    //     $message = "Hi {$data['name']} \n Queue No.{$data['token_with_acronym']}{$data['token']}";
    // else
    //       $message = "Hi {$data['name']} \n Reminder Queue No.{$data['token_with_acronym']}{$data['token']}";


    //     $sid = $details->sms_api_key;
    //     $token =  $details->sms_api_value;
    //     $fromNumber = $details->contact;

    //     try {
    //         $client = new Client($sid, $token);
    //         $client->messages->create($receiverNumber, [
    //             'from' => $fromNumber,
    //             'body' => str_replace("\n", PHP_EOL, $message)
    //         ]);

    //         return 'SMS Sent Successfully.';
    //     } catch (Exception $e) {
    //         return 'Error: ' . $e->getMessage();
    //     }
    // }
    public static function sendSmsWhatsApp($teamId, $data)
    {
        // $receiverNumber = '+91'.$data['phone'];
        // $details = self::viewDetails($teamId,self::TYPE_WHATSAPP);
        // $message = "Hi {$data['name']} \n Queue No.{$data['token_with_acronym']}{$data['token']}";

        // $sid = $details->sms_api_key;
        // $token =  $details->sms_api_value;
        // $fromNumber = $details->contact;
        // try {
        //     $client = new Client($sid, $token);
        //     $client->messages->create('whatsapp:'.$receiverNumber, [
        //         'from' => $fromNumber,
        //         'body' => str_replace("\n", PHP_EOL, $message)
        //     ]);

        //     return 'SMS Sent Successfully.';
        // } catch (Exception $e) {

        //     Log::error("WhatsApp Error: " . $e->getMessage());

        //     SmsReport::create([
        //         'team_id' => $teamId,
        //         'location_id' => $data['location_id'],
        //         'message' => $message,
        //         'contact' => $receiverNumber,
        //         'status' => 'failed',
        //         'channel' => 'whatsapp',
        //         'failed_reason'=>$e->getMessage(),
        //     ]);
        //     return 'Error: ' . $e->getMessage();
        // }
    }

    public static function calculateSmsSegments($message)
    {
        $length = mb_strlen($message, 'UTF-8'); // use mb_strlen to handle multi-byte characters

        if ($length <= 160) {
            return 1;
        }

        // For concatenated SMS, segment size is 153
        return ceil($length / 153);
    }

    public static function sendInteraktMessage(array $data = [])
    {

        try {
            $url = $data['url'] ?? null;
            $token = $data['token'] ?? null;
            $to = $data['fullPhoneNumber'] ?? null;
            $bodyValues = (array)$data['bodyValues'] ?? [];
            $templateName = $data['template'] ?? null;
            $teamId = $data['team_id'] ?? null;
            $locationId = $data['location_id'] ?? null;
            $queue_id = $data['queue_id'] ?? null;
            $queue_storage_id = $data['queue_storage_id'] ?? null;
            $user_id = $data['user_id'] ?? null;
            $customer_id = $data['customer_id'] ?? null;
            $booking_id = $data['booking_id'] ?? null;
            $type = $data['type'] ?? 'triggered';
            $event = $data['event_name'] ?? 'other';

            if (!$token || !$to || !$templateName || empty($bodyValues)) {
                return [
                    'status' => 'failed',
                    'error' => 'Missing required fields (token/ phone number / template / body values)'
                ];
            }
            Log::info('to: ' . $to);
            Log::info('template name: ' . $templateName);
            Log::info('bodyValues: ' . json_encode($bodyValues));
            $response = app(InteraktService::class)->sendTemplateMessageToUser(
                $url,
                $token,
                $to,
                $bodyValues,
                $templateName
            );

            $status = $response->successful() ? 'sent' : 'failed';

            $responseBody = $response->json();

            $message = $responseBody['message'] ?? null;
            if ($status == 'sent') {
                MessageDetail::create([
                    'team_id' => $teamId ?? null,
                    'location_id' => $data['location_id'] ?? null,
                    'user_id' => $user_id ?? null,
                    'customer_id' => $customer_id ?? null,
                    'queue_id' => $queue_id ?? null,
                    'queue_storage_id' => $queue_storage_id ?? null,
                    'booking_id' => $booking_id ?? null,
                    'message' => '',
                    'contact' => $to,
                    'status' => $status,
                    'channel' => 'whatsapp',
                    'type' => $type,
                    'event_name' => $event,
                    'response_status' => $response,
                    'segment' => null
                ]);

                // $customerData = Customer::where('id', $data['customer_id'])->value('team_id', 'location_id', 'name', 'email', 'phone');

                // ActivityLog::storeLog($teamId,  $data['customer_id'] ?? null, null, null, 'Message sent',  null, ActivityLog::WHATSAPP_MESSAGE, null, json_encode($customerData, true));
                return true;
            } else {
                Log::error('Interakt Send Message failed: ' . $message);

                MessageDetail::create([
                    'team_id' => $data['team_id'] ?? tenant('id'),
                    'location_id' => $data['location_id'] ?? null,
                    'user_id' => $user_id ?? null,
                    'customer_id' => $customer_id ?? null,
                    'queue_id' => $queue_id ?? null,
                    'queue_storage_id' => $queue_storage_id ?? null,
                    'booking_id' => $booking_id ?? null,
                    'message' => '',
                    'failed_reason' => $response,
                    'contact' => $to ?? '',
                    'status' => 'failed',
                    'channel' => 'whatsapp',
                    'type' => $type ?? 'triggered',
                    'event_name' => $event ?? 'other',
                    'response_status' => $response
                ]);
                return false;
            }


            // return [
            //     'status' => $status,
            //     'response' => $responseBody
            // ];
        } catch (\Throwable $e) {
            Log::error('Interakt Send Message Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'payload' => $data
            ]);

            // $customerData = Customer::where('id', $data['customer_id'])->value('team_id', 'location_id', 'name', 'email', 'phone');

            // ActivityLog::storeLog($teamId,  $data['customer_id'] ?? null, null, null, 'Failed to send message',  null, ActivityLog::WHATSAPP_MESSAGE, null, json_encode($customerData, true));

            MessageDetail::create([
                'team_id' => $teamId ?? tenant('id'),
                'location_id' => $locationId ?? null,
                'user_id' => $user_id ?? null,
                'customer_id' => $customer_id ?? null,
                'queue_id' => $queue_id ?? null,
                'queue_storage_id' => $queue_storage_id ?? null,
                'booking_id' => $booking_id ?? null,
                'message' => '',
                'failed_reason' => $e->getMessage(),
                'contact' => $to ?? '',
                'status' => 'failed',
                'channel' => 'whatsapp',
                'type' => $type ?? 'triggered',
                'event_name' => $event ?? 'other',
                'response_status' => $response
            ]);
            return false;
            return [
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }

    public static function sendTemplateMessage($phoneNumber, $templateName, array $params, $apiKey, $source, $srcName)
{
    $apiUrl = 'https://api.gupshup.io/wa/api/v1/template/msg';

    $response = Http::asForm()
        ->withHeaders([
            'apikey' => $apiKey,
        ])
        ->post($apiUrl, [
            'channel'     => 'whatsapp',
            'source'      => $source,
            'destination' => $phoneNumber,
            'src.name'    => $srcName,
            'template'    => json_encode([
                'id'     => $templateName,
                'params' => $params,
            ]),
        ]);

    return $response->json();
}
}
