<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use App\Models\NotificationTemplate;
use App\Models\SlackTemplate;
use Auth;
use App\Mail\SendNotificationMail;
use App\Jobs\SendEmailJob;
use App\Services\SlackService;



class SmtpDetails extends Model
{
    const ENABLE = 1;

    use HasFactory;
    protected $table  = 'smtp_details';

    protected $fillable =['team_id','location_id','from_name','from_email','hostname','port','username','password','encryption','created_at','updated_at'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }


    public static function viewDetails($teamId){
        if (Auth::user()->hasRole('Super Admin')) {
            return self::where('team_id', null)->first();
        }
       return self::where('team_id', $teamId)->first();
    }





//  public static function sendMail($data, $title, $template, $teamId,$logData= [])
//    {


//     $fields = ['team_id']; // Default field

//     if ($title === 'ticket created') {
//         $fields = array_merge($fields, ['ticket_notification_status', 'ticket_notification_subject', 'ticket_notification']);
//     } elseif ($title === 'call') {
//         $fields = array_merge($fields, ['service_call_notification_status', 'service_call_notification_subject', 'service_call_notification']);
//     } elseif ($title === 'recall') {
//         $fields = array_merge($fields, ['service_recall_notification_status', 'service_recall_notification_subject', 'service_recall_notification']);
//     } elseif ($title === 'call skip') {
//         $fields = array_merge($fields, ['call_skip_notification_status', 'call_skip_notification_subject', 'call_skip_notification']);
//     } elseif ($title === 'rating survey') {
//         $fields = array_merge($fields, ['feedback_notification_status', 'feedback_notification_subject', 'feedback_notification']);
//     } elseif ($title === 'booking confirmed') {
//         $fields = array_merge($fields, ['booking_confirmed_notification_status', 'booking_confirmed_notification_subject', 'booking_confirmed_notification']);
//     } elseif ($title === 'booking rescheduled') {
//         $fields = array_merge($fields, ['booking_reschedule_notification_status', 'booking_reschedule_notification_subject', 'booking_reschedule_notification']);
//     } elseif ($title === 'booking cancelled') {
//         $fields = array_merge($fields, ['booking_cancel_notification_status', 'booking_cancel_notification_subject', 'booking_cancel_notification']);
//     } elseif ($title === 'reminder') {
//         $fields = array_merge($fields, ['reminder_notification_status', 'reminder_notification_subject', 'reminder_notification']);
//     } elseif ($title === 'edit partner') {
//         $fields = array_merge($fields, ['edit_partner_notification_status', 'edit_partner_notification_subject', 'edit_partner_notification']);
//     } elseif ($title === 'delete partner') {
//         $fields = array_merge($fields, ['delete_partner_notification_status', 'delete_partner_notification_subject', 'delete_partner_notification']);
//     } elseif ($title === 'activate partner') {
//         $fields = array_merge($fields, ['activate_partner_notification_status', 'activate_partner_notification_subject', 'activate_partner_notification']);
//     } elseif ($title === 'deactivate partner') {
//         $fields = array_merge($fields, ['deactivate_partner_notification_status', 'deactivate_partner_notification_subject', 'deactivate_partner_notification']);
//     } elseif ($title === 'reset password') {
//         $fields = array_merge($fields, ['reset_password_notification_status', 'reset_password_notification_subject', 'reset_password_notification']);
//     }
//     elseif ($title === 'virtual meeting') {
//         $fields = array_merge($fields, ['virtual_meeting_notification_status', 'virtual_meeting_notification_subject', 'virtual_meeting_notification']);
//     }


//         // Retrieve the notification template for the specified team
//         $getTemplate = NotificationTemplate::where(function ($query) use ($teamId,$data) {
//             if (Auth::check() && Auth::user()->hasRole('Super Admin')) {
//                 $query->where('team_id', null); // Super Admin gets templates with null team_id
//             } else {
//                 $query->where('team_id', $teamId)->where('location_id',$data['locations_id']); // Other users get templates for their team
//             }
//         })->select($fields)->first();

//         $button =false;
//         if (isset($getTemplate)) {
//             if ($title === 'ticket created' && $getTemplate->ticket_notification_status == SmtpDetails::ENABLE) {
//                 // Update template and title if provided in the notification settings
//                 $template = !empty($getTemplate->ticket_notification) ? $getTemplate->ticket_notification : $template;
//                 $title = !empty($getTemplate->ticket_notification_subject) ? $getTemplate->ticket_notification_subject : $title;
//             }
//             elseif ($title === 'call' && $getTemplate->service_call_notification_status == SmtpDetails::ENABLE) {
//                 // Update template and title if provided in the notification settings
//                 $template = !empty($getTemplate->service_call_notification_status) ? $getTemplate->service_call_notification : $template;
//                 $title = !empty($getTemplate->service_call_notification) ? $getTemplate->service_call_notification_subject : $title;
//             }
//             elseif ($title === 'recall' && $getTemplate->service_recall_notification_status == SmtpDetails::ENABLE) {
//                 // Update template and title if provided in the notification settings
//                 $template = !empty($getTemplate->service_recall_notification_status) ? $getTemplate->service_recall_notification : $template;
//                 $title = !empty($getTemplate->service_recall_notification) ? $getTemplate->service_recall_notification_subject : $title;
//             }
//             elseif ($title === 'call skip' && $getTemplate->call_skip_notification_status == SmtpDetails::ENABLE) {
//                 // Update template and title if provided in the notification settings
//                 $template = !empty($getTemplate->call_skip_notification_subject) ? $getTemplate->call_skip_notification : $template;
//                 $title = !empty($getTemplate->call_skip_notification) ? $getTemplate->call_skip_notification_subject : $title;
//             }
//             elseif ($title == 'rating survey' && $getTemplate->feedback_notification_status == SmtpDetails::ENABLE) {
//                 // Update template and title if provided in the notification settings
//                 $template = !empty($getTemplate->feedback_notification_subject) ? $getTemplate->feedback_notification : $template;
//                 $title = !empty($getTemplate->feedback_notification) ? $getTemplate->feedback_notification_subject : $title;
//             }
//             elseif ($title == 'booking confirmed' && $getTemplate->booking_confirmed_notification_status == SmtpDetails::ENABLE) {
//                 // Update template and title if provided in the notification settings
//                 $template = !empty($getTemplate->booking_confirmed_notification) ? $getTemplate->booking_confirmed_notification : $template;
//                 $title = !empty($getTemplate->booking_confirmed_notification_subject) ? $getTemplate->booking_confirmed_notification_subject : $title;
//                 $button =true;
//             }
//             elseif ($title == 'booking rescheduled' && $getTemplate->booking_reschedule_notification_status == SmtpDetails::ENABLE) {
//                 // Update template and title if provided in the notification settings
//                 $template = !empty($getTemplate->booking_reschedule_notification) ? $getTemplate->booking_reschedule_notification : $template;
//                 $title = !empty($getTemplate->booking_reschedule_notification_subject) ? $getTemplate->booking_reschedule_notification_subject : $title;
//                 $button =true;
//             }
//             elseif ($title == 'booking cancelled' && $getTemplate->booking_cancel_notification_status == SmtpDetails::ENABLE) {
//                 // Update template and title if provided in the notification settings
//                 $template = !empty($getTemplate->booking_cancel_notification) ? $getTemplate->booking_cancel_notification : $template;
//                 $title = !empty($getTemplate->booking_cancel_notification_subject) ? $getTemplate->booking_cancel_notification_subject : $title;
//                 // $button =true;
//             }
//             elseif ($title == 'reminder' && $getTemplate->reminder_notification_status == SmtpDetails::ENABLE) {
//                 // Update template and title if provided in the notification settings
//                 $template = !empty($getTemplate->reminder_notification) ? $getTemplate->reminder_notification : $template;
//                 $title = !empty($getTemplate->reminder_notification_subject) ? $getTemplate->reminder_notification_subject : $title;
//                 // $button =true;
//             }
//             elseif ($title == 'edit partner' && $getTemplate->edit_partner_notification_status == SmtpDetails::ENABLE) {
//                 // Update template and title if provided in the notification settings
//                 $template = !empty($getTemplate->edit_partner_notification) ? $getTemplate->edit_partner_notification : $template;
//                 $title = !empty($getTemplate->edit_partner_notification_subject) ? $getTemplate->edit_partner_notification_subject : $title;
//                 // $button =true;
//             }
//             elseif ($title == 'delete partner' && $getTemplate->delete_partner_notification_status == SmtpDetails::ENABLE) {
//                 // Update template and title if provided in the notification settings
//                 $template = !empty($getTemplate->delete_partner_notification) ? $getTemplate->delete_partner_notification : $template;
//                 $title = !empty($getTemplate->delete_partner_notification_subject) ? $getTemplate->delete_partner_notification_subject : $title;
//                 // $button =true;
//             }
//             elseif ($title == 'activate partner' && $getTemplate->activate_partner_notification_status == SmtpDetails::ENABLE) {
//                 // Update template and title if provided in the notification settings
//                 $template = !empty($getTemplate->activate_partner_notification) ? $getTemplate->activate_partner_notification : $template;
//                 $title = !empty($getTemplate->activate_partner_notification_subject) ? $getTemplate->activate_partner_notification_subject : $title;
//                 // $button =true;
//             }
//             elseif ($title == 'deactivate partner' && $getTemplate->dectivate_partner_notification_status == SmtpDetails::ENABLE) {
//                 // Update template and title if provided in the notification settings
//                 $template = !empty($getTemplate->deactivate_partner_notification) ? $getTemplate->deactivate_partner_notification : $template;
//                 $title = !empty($getTemplate->deactivate_partner_notification_subject) ? $getTemplate->deactivate_partner_notification_subject : $title;
//                 // $button =true;
//             }
//             elseif ($title == 'reset password' && $getTemplate->reset_password_notification_status == SmtpDetails::ENABLE) {
//                 // Update template and title if provided in the notification settings
//                 $template = !empty($getTemplate->reset_password_notification) ? $getTemplate->reset_password_notification : $template;
//                 $title = !empty($getTemplate->reset_password_notification_subject) ? $getTemplate->reset_password_notification_subject : $title;
//                 // $button =true;
//             }
//               elseif ($title == 'virtual meeting' && $getTemplate->virtual_meeting_notification_status == SmtpDetails::ENABLE) {
//                 // Update template and title if provided in the notification settings
//                 $template = !empty($getTemplate->virtual_meeting_notification) ? $getTemplate->virtual_meeting_notification : $template;
//                 $title = !empty($getTemplate->virtual_meeting_notification_subject) ? $getTemplate->virtual_meeting_notification_subject : $title;
//                 // $button =true;
//             }
//         }

//         // Prepare SMTP details
//         $smtpDetails = [];

//         $team=tenant('name');
//         $panel_name=isset($team) != null ? $team : '';

//         $details = self::where('team_id', $teamId)->where('location_id',$data['locations_id'])->first();

//         $siteDetail = SiteDetail::where('team_id', $teamId)->where('location_id',$data['locations_id'])->select('business_logo')->first();


//         $smtpDetails['from_email'] = $details->from_email ?? 'test@gmail.com';
//         $smtpDetails['from_name'] = $details->from_name ?? "john";


//         // Set default recipient email if not provided
//         if (empty($data['to_mail'])) {
//             $data['to_mail'] = $details->from_email;
//         }
//         // Replace placeholders in the template with actual
//         $logo = isset($siteDetail) && $siteDetail->business_logo ? url('storage/' . $siteDetail->business_logo) : '';

//         $headerLogo = '<img src="' . $logo . '" alt="logo" width="250">';

//         $templateBody = self::replaceTemplatePlaceholders(nl2br($template), $data, $teamId);

//         // Footer section
//         // $footer = "
//         //     <p>Have a pleasant day,</p>
//         //     <p>Best regards,</p>
//         //     <p><b>{$panel_name} Team</b></p>
//         // ";

//         // Check for edit_cancel_book_cus condition
//         $accountSetting = AccountSetting::where('team_id', $teamId)->where('location_id',$data['locations_id'])->where('slot_type', AccountSetting::BOOKING_SLOT)->select('edit_cancel_book_cus', 'req_accept_mode')->first();

//         $actionButtons = '<div style="display:flex; margin:10px;">';

//         if ($accountSetting && $accountSetting->edit_cancel_book_cus == 1 && $button) {
//             $editUrl = url('edit-booking', ['id' => base64_encode($data['booking_id'])]);
//             $cancelUrl = url('booking-cancelled', ['id' => base64_encode($data['booking_id'])]);
//             $confirmedUrl = url('booking-confirmed', ['id' => base64_encode($data['booking_id'])]);

//             $actionButtons .= "
//                 <div>
//                     <a href='{$confirmedUrl}' style='padding:10px; background-color:blue; color:white; text-decoration:none; border-radius:5px;'>Reschedule</a>
//                     <a href='{$confirmedUrl}' style='padding:10px; background-color:red; color:white; text-decoration:none; border-radius:5px;'>Cancel</a>
//                 </div>
//             ";
//         }

//         // if ($accountSetting && $accountSetting->req_accept_mode == AccountSetting::AUTO_CONFIRM && $button) {
//         //     $confirmedUrl = url('booking-confirmed', ['id' => base64_encode($data['booking_id'])]);

//         //     $actionButtons .= "
//         //         <div style='margin-left:5px;'>
//         //             <a href='{$confirmedUrl}' style='padding:10px;background-color:green; color:white; text-decoration:none; border-radius:5px;'>Confirmed</a>
//         //         </div>
//         //     ";
//         // }

//         $actionButtons .= '</div>';

//         // Build final HTML email
//         $templateContent = '
//         <div style="background:#e8e8e8;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen,Ubuntu,Cantarell,Fira Sans,Droid Sans,Helvetica Neue,sans-serif;font-size:13px;line-height:1.4;padding:2% 7%">

//               <img id="Qwaiting" src="' .$logo. '" alt="logo" class="CToWUd" style="vertical-align:middle;" width="100">

//         <div style="background:#fff;border-top-color:#6e8cce;border-top-style:solid;border-top-width:4px;margin:25px auto;
//         border-radius: 8px;">
//             <div style="border-color:#e5e5e5;border-style:none solid solid;border-width:2px;padding:7%">
//             <div>



//         </div>

//             '.$templateBody.'

//             '. $actionButtons .'

//             </div>
//         </div>
//         <div style="text-align:center" align="center">
//             <p style="color:#999;font-size:11px;line-height:1.4;margin:5px 0">Copyright ' .date("Y"). ' © Qwaiting Inc. All Rights Reserved.</p>
//         </div>
//         </div>';


//         try {


//             // Send the email using the specified template and SMTP settings


//             \Log::info('first check: ' . filter_var($data['to_mail'], FILTER_VALIDATE_EMAIL));

//             if (filter_var($data['to_mail'], FILTER_VALIDATE_EMAIL)) {
//                 \Log::info('second check: ' . $details->hostname.','.$details->encryption.','.$details->username.','.$details->password.','.$details->from_email.','.$details->from_name);
//                 if(!empty($details->hostname) && !empty($details->port) &&  !empty($details->username) && !empty($details->password) && !empty($details->from_email) &&  !empty($details->from_name)){
//                     Config::set('mail.mailers.smtp.transport', 'smtp');
//                     Config::set('mail.mailers.smtp.host', trim($details->hostname));
//                     Config::set('mail.mailers.smtp.port', trim($details->port));
//                     Config::set('mail.mailers.smtp.encryption', trim($details->encryption ?? 'ssl'));
//                     Config::set('mail.mailers.smtp.username', trim($details->username));
//                     Config::set('mail.mailers.smtp.password', trim($details->password));

//                     Config::set('mail.from.address', trim($details->from_email));
//                     Config::set('mail.from.name', trim($details->from_name));

//                     \Log::info('send mail check');
//                     Mail::html( $templateContent, function ($message) use ($data, $title, $smtpDetails) {
//                         $message->from($smtpDetails['from_email'], $smtpDetails['from_name']);
//                         $message->to($data['to_mail'])->subject($title);

//                     });
//                     if(!empty($logData)){
//                        $logData['channel'] = 'email';
//                         $logData['message'] = $templateBody ?? '';
//                         $logData['event_name'] = $title;
//                         $logData['status'] = MessageDetail::SENT_STATUS;
//                         MessageDetail::storeLog($logData);
//                     }
//                 }


//         }

//         } catch (\Throwable $e) {
//             // Log the error and return the message
//             \Log::error('Error sending email: ' . $e->getMessage());
//              if(!empty($logData)){
//                     $logData['channel'] = 'email';
//                     $logData['message'] = $templateBody ?? '';
//                     $logData['event_name'] = $title;
//                     $logData['status'] = MessageDetail::FAILED_STATUS;
//                     $logData['failed_reason'] = $e->getMessage();
//                     MessageDetail::storeLog($logData);
//              }
//             // return $e->getMessage();
//         }
//     }

   public static function sendMail($data, $title, $template, $teamId,$logData= [])
   {


    $fields = ['team_id']; // Default field

    if ($title === 'ticket created') {
        $fields = array_merge($fields, ['ticket_notification_status', 'ticket_notification_subject', 'ticket_notification']);
    } elseif ($title === 'call') {
        $fields = array_merge($fields, ['service_call_notification_status', 'service_call_notification_subject', 'service_call_notification']);
    } elseif ($title === 'recall') {
        $fields = array_merge($fields, ['service_recall_notification_status', 'service_recall_notification_subject', 'service_recall_notification']);
    } elseif ($title === 'call skip') {
        $fields = array_merge($fields, ['call_skip_notification_status', 'call_skip_notification_subject', 'call_skip_notification']);
    } elseif ($title === 'rating survey') {
        $fields = array_merge($fields, ['feedback_notification_status', 'feedback_notification_subject', 'feedback_notification']);
    } elseif ($title === 'booking confirmed') {
        $fields = array_merge($fields, ['booking_confirmed_notification_status', 'booking_confirmed_notification_subject', 'booking_confirmed_notification']);
    } elseif ($title === 'booking rescheduled') {
        $fields = array_merge($fields, ['booking_reschedule_notification_status', 'booking_reschedule_notification_subject', 'booking_reschedule_notification']);
    } elseif ($title === 'booking cancelled') {
        $fields = array_merge($fields, ['booking_cancel_notification_status', 'booking_cancel_notification_subject', 'booking_cancel_notification']);
    } elseif ($title === 'reminder') {
        $fields = array_merge($fields, ['reminder_notification_status', 'reminder_notification_subject', 'reminder_notification']);
    } elseif ($title === 'edit partner') {
        $fields = array_merge($fields, ['edit_partner_notification_status', 'edit_partner_notification_subject', 'edit_partner_notification']);
    } elseif ($title === 'delete partner') {
        $fields = array_merge($fields, ['delete_partner_notification_status', 'delete_partner_notification_subject', 'delete_partner_notification']);
    } elseif ($title === 'activate partner') {
        $fields = array_merge($fields, ['activate_partner_notification_status', 'activate_partner_notification_subject', 'activate_partner_notification']);
    } elseif ($title === 'deactivate partner') {
        $fields = array_merge($fields, ['deactivate_partner_notification_status', 'deactivate_partner_notification_subject', 'deactivate_partner_notification']);
    } elseif ($title === 'reset password') {
        $fields = array_merge($fields, ['reset_password_notification_status', 'reset_password_notification_subject', 'reset_password_notification']);
    }
    elseif ($title === 'virtual meeting') {
        $fields = array_merge($fields, ['virtual_meeting_notification_status', 'virtual_meeting_notification_subject', 'virtual_meeting_notification']);
    } elseif ($title === 'payment_success') {
        $fields = array_merge($fields, ['stripe_sms_notification_status', 'stripe_sms_notification_subject', 'stripe_sms_notification']);
    }
    //  elseif ($title === 'payment_success') {
    //     $fields = array_merge($fields, ['stripe_sms_notification_status', 'stripe_sms_notification_subject', 'stripe_sms_notification']);
    // }


        // Retrieve the notification template for the specified team
        $getTemplate = NotificationTemplate::where(function ($query) use ($teamId,$data) {
            if (Auth::check() && Auth::user()->hasRole('Super Admin')) {
                $query->where('team_id', null); // Super Admin gets templates with null team_id
            } else {
                $query->where('team_id', $teamId)->where('location_id',$data['locations_id']); // Other users get templates for their team
            }
        })->select($fields)->first();

        $button =false;
        $titleName = '';
        if (isset($getTemplate)) {
            if ($title === 'ticket created' && $getTemplate->ticket_notification_status == SmtpDetails::ENABLE) {
                // Update template and title if provided in the notification settings
                $template = !empty($getTemplate->ticket_notification) ? $getTemplate->ticket_notification : $template;
                $titleName = !empty($getTemplate->ticket_notification_subject) ? $getTemplate->ticket_notification_subject : $title;
            }
            elseif ($title === 'call' && $getTemplate->service_call_notification_status == SmtpDetails::ENABLE) {
                // Update template and title if provided in the notification settings
                $template = !empty($getTemplate->service_call_notification_status) ? $getTemplate->service_call_notification : $template;
                $titleName = !empty($getTemplate->service_call_notification) ? $getTemplate->service_call_notification_subject : $title;
            }
            elseif ($title === 'recall' && $getTemplate->service_recall_notification_status == SmtpDetails::ENABLE) {
                // Update template and title if provided in the notification settings
                $template = !empty($getTemplate->service_recall_notification_status) ? $getTemplate->service_recall_notification : $template;
                $titleName = !empty($getTemplate->service_recall_notification) ? $getTemplate->service_recall_notification_subject : $title;
            }
            elseif ($title === 'call skip' && $getTemplate->call_skip_notification_status == SmtpDetails::ENABLE) {
                // Update template and title if provided in the notification settings
                $template = !empty($getTemplate->call_skip_notification_subject) ? $getTemplate->call_skip_notification : $template;
                $titleName = !empty($getTemplate->call_skip_notification) ? $getTemplate->call_skip_notification_subject : $title;
            }
            elseif ($title == 'rating survey' && $getTemplate->feedback_notification_status == SmtpDetails::ENABLE) {
                // Update template and title if provided in the notification settings
                $template = !empty($getTemplate->feedback_notification_subject) ? $getTemplate->feedback_notification : $template;
                $titleName = !empty($getTemplate->feedback_notification) ? $getTemplate->feedback_notification_subject : $title;
            }
            elseif ($title == 'booking confirmed' && $getTemplate->booking_confirmed_notification_status == SmtpDetails::ENABLE) {
                // Update template and title if provided in the notification settings
                $template = !empty($getTemplate->booking_confirmed_notification) ? $getTemplate->booking_confirmed_notification : $template;
                $titleName = !empty($getTemplate->booking_confirmed_notification_subject) ? $getTemplate->booking_confirmed_notification_subject : $title;
                $button =true;
            }
            elseif ($title == 'booking rescheduled' && $getTemplate->booking_reschedule_notification_status == SmtpDetails::ENABLE) {
                // Update template and title if provided in the notification settings
                $template = !empty($getTemplate->booking_reschedule_notification) ? $getTemplate->booking_reschedule_notification : $template;
                $titleName = !empty($getTemplate->booking_reschedule_notification_subject) ? $getTemplate->booking_reschedule_notification_subject : $title;
                $button =true;
            }
            elseif ($title == 'booking cancelled' && $getTemplate->booking_cancel_notification_status == SmtpDetails::ENABLE) {
                // Update template and title if provided in the notification settings
                $template = !empty($getTemplate->booking_cancel_notification) ? $getTemplate->booking_cancel_notification : $template;
                $titleName = !empty($getTemplate->booking_cancel_notification_subject) ? $getTemplate->booking_cancel_notification_subject : $title;
                // $button =true;
            }
            elseif ($title == 'reminder' && $getTemplate->reminder_notification_status == SmtpDetails::ENABLE) {
                // Update template and title if provided in the notification settings
                $template = !empty($getTemplate->reminder_notification) ? $getTemplate->reminder_notification : $template;
                $titleName = !empty($getTemplate->reminder_notification_subject) ? $getTemplate->reminder_notification_subject : $title;
                // $button =true;
            }
            elseif ($title == 'edit partner' && $getTemplate->edit_partner_notification_status == SmtpDetails::ENABLE) {
                // Update template and title if provided in the notification settings
                $template = !empty($getTemplate->edit_partner_notification) ? $getTemplate->edit_partner_notification : $template;
                $titleName = !empty($getTemplate->edit_partner_notification_subject) ? $getTemplate->edit_partner_notification_subject : $title;
                // $button =true;
            }
            elseif ($title == 'delete partner' && $getTemplate->delete_partner_notification_status == SmtpDetails::ENABLE) {
                // Update template and title if provided in the notification settings
                $template = !empty($getTemplate->delete_partner_notification) ? $getTemplate->delete_partner_notification : $template;
                $titleName = !empty($getTemplate->delete_partner_notification_subject) ? $getTemplate->delete_partner_notification_subject : $title;
                // $button =true;
            }
            elseif ($title == 'activate partner' && $getTemplate->activate_partner_notification_status == SmtpDetails::ENABLE) {
                // Update template and title if provided in the notification settings
                $template = !empty($getTemplate->activate_partner_notification) ? $getTemplate->activate_partner_notification : $template;
                $titleName = !empty($getTemplate->activate_partner_notification_subject) ? $getTemplate->activate_partner_notification_subject : $title;
                // $button =true;
            }
            elseif ($title == 'deactivate partner' && $getTemplate->dectivate_partner_notification_status == SmtpDetails::ENABLE) {
                // Update template and title if provided in the notification settings
                $template = !empty($getTemplate->deactivate_partner_notification) ? $getTemplate->deactivate_partner_notification : $template;
                $titleName = !empty($getTemplate->deactivate_partner_notification_subject) ? $getTemplate->deactivate_partner_notification_subject : $title;
                // $button =true;
            }
            elseif ($title == 'reset password' && $getTemplate->reset_password_notification_status == SmtpDetails::ENABLE) {
                // Update template and title if provided in the notification settings
                $template = !empty($getTemplate->reset_password_notification) ? $getTemplate->reset_password_notification : $template;
                $titleName = !empty($getTemplate->reset_password_notification_subject) ? $getTemplate->reset_password_notification_subject : $title;
                // $button =true;
            }
            elseif ($title == 'virtual meeting' && $getTemplate->virtual_meeting_notification_status == SmtpDetails::ENABLE) {
                // Update template and title if provided in the notification settings
                $template = !empty($getTemplate->virtual_meeting_notification) ? $getTemplate->virtual_meeting_notification : $template;
                $titleName = !empty($getTemplate->virtual_meeting_notification_subject) ? $getTemplate->virtual_meeting_notification_subject : $title;
                // $button =true;
            } elseif ($title == 'payment_success' && $getTemplate->stripe_sms_notification_status == SmtpDetails::ENABLE) {
                $template = !empty($getTemplate->stripe_sms_notification) ? $getTemplate->stripe_sms_notification : $template;
                $titleName = !empty($getTemplate->stripe_sms_notification_subject) ? $getTemplate->stripe_sms_notification_subject : $title;
            }
        }
        $enableSlack = SlackSetting::where('team_id', $teamId)
            ->where('location_id', $data['locations_id'])
            ->value('status');
        $slackTemplateDetail = $slackTemplate = '';
        if($enableSlack){
           $slackTemplateDetail = SlackTemplate::where('team_id', $teamId)->where('location_id',$data['locations_id'])->first();
           \Log::info('slack template detail: ' . json_encode($slackTemplateDetail));
           if ($slackTemplateDetail) {
                if ($title == 'ticket created' && $slackTemplateDetail->ticket_generation_message_status == SmtpDetails::ENABLE) {
                    $slackTemplate = $slackTemplateDetail->ticket_generation_message ?: $title;
                     \Log::info('test title ' . $title);
                     \Log::info('test slackTemplate ' . $slackTemplate);
                } elseif ($title == 'booking confirmed' && $slackTemplateDetail->new_booking_sms_message_status == SmtpDetails::ENABLE) {
                    $slackTemplate = $slackTemplateDetail->new_booking_sms_message ?? $title;

                }elseif ($title == 'call' && $slackTemplateDetail->next_call_message_status == SmtpDetails::ENABLE) {
                    $slackTemplate = $slackTemplateDetail->next_call_message ?: $title;

                } elseif ($title == 'rating survey' && $slackTemplateDetail->feedback_sms_message_status == SmtpDetails::ENABLE) {
                    $slackTemplate = $slackTemplateDetail->feedback_sms_message ?: $title;
                }
                elseif ($title == 'call skip' && $slackTemplateDetail->skip_call_message_status == SmtpDetails::ENABLE) {
                    $slackTemplate = $slackTemplateDetail->skip_call_message ?: $title;
                }
                elseif ($title == 'recall' && $slackTemplateDetail->recall_message_status == SmtpDetails::ENABLE) {
                    $slackTemplate = $slackTemplateDetail->recall_message ?: $title;

                }
                elseif ($title == 'booking rescheduled' && $slackTemplateDetail->reschedule_booking_sms_status == SmtpDetails::ENABLE) {
                    $slackTemplate = $slackTemplateDetail->reschedule_booking_sms ?: $title;
                } elseif ($title == 'booking cancelled' && $slackTemplateDetail->cancel_booking_sms_status == SmtpDetails::ENABLE) {
                    $slackTemplate = $slackTemplateDetail->cancel_booking_sms ?: $title;
                } elseif ($title == 'reminder' && $slackTemplateDetail->reminder_message_status == SmtpDetails::ENABLE) {
                    $slackTemplate = $slackTemplateDetail->reminder_message ?: $title;
                }
            }
        }
           \Log::info('title ' . $title);
           \Log::info('slack template: ' . $slackTemplate);


        // Prepare SMTP details
        $smtpDetails = [];

        $team=tenant('name');
        $panel_name=isset($team) != null ? $team : '';

        $details = self::where('team_id', $teamId)->where('location_id',$data['locations_id'])->first();

        $siteDetail = SiteDetail::where('team_id', $teamId)->where('location_id',$data['locations_id'])->select('business_logo')->first();


        $smtpDetails['from_email'] = $details->from_email ?? 'test@gmail.com';
        $smtpDetails['from_name'] = $details->from_name ?? "john";


        // Set default recipient email if not provided
        if (empty($data['to_mail'])) {
            $data['to_mail'] = $details->from_email;
        }
        // Replace placeholders in the template with actual
        $logo = isset($siteDetail) && $siteDetail->business_logo ? url('storage/' . $siteDetail->business_logo) : '';

        $headerLogo = '<img src="' . $logo . '" alt="logo" width="250">';

        $templateBody = self::replaceTemplatePlaceholders(nl2br($template), $data, $teamId);
        $slacktemplateBody = self::replaceTemplatePlaceholders($slackTemplate, $data, $teamId);

        // Footer section
        // $footer = "
        //     <p>Have a pleasant day,</p>
        //     <p>Best regards,</p>
        //     <p><b>{$panel_name} Team</b></p>
        // ";

        // Check for edit_cancel_book_cus condition
        $accountSetting = AccountSetting::where('team_id', $teamId)->where('location_id',$data['locations_id'])->where('slot_type', AccountSetting::BOOKING_SLOT)->select('edit_cancel_book_cus', 'req_accept_mode')->first();

        $actionButtons = '<div style="display:flex; margin:10px;">';

        if ($accountSetting && $accountSetting->edit_cancel_book_cus == 1 && $button) {
            $editUrl = url('edit-booking', ['id' => base64_encode($data['booking_id'])]);
            $cancelUrl = url('booking-cancelled', ['id' => base64_encode($data['booking_id'])]);
            $confirmedUrl = url('booking-confirmed', ['id' => base64_encode($data['booking_id'])]);

            $actionButtons .= "
                <div>
                    <a href='{$confirmedUrl}' style='padding:10px; background-color:blue; color:white; text-decoration:none; border-radius:5px;'>Reschedule</a>
                    <a href='{$confirmedUrl}' style='padding:10px; background-color:red; color:white; text-decoration:none; border-radius:5px;'>Cancel</a>
                </div>
            ";
        }

        // if ($accountSetting && $accountSetting->req_accept_mode == AccountSetting::AUTO_CONFIRM && $button) {
        //     $confirmedUrl = url('booking-confirmed', ['id' => base64_encode($data['booking_id'])]);

        //     $actionButtons .= "
        //         <div style='margin-left:5px;'>
        //             <a href='{$confirmedUrl}' style='padding:10px;background-color:green; color:white; text-decoration:none; border-radius:5px;'>Confirmed</a>
        //         </div>
        //     ";
        // }

        $actionButtons .= '</div>';

        // Build final HTML email
        $templateContent = '
        <div style="background:#e8e8e8;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen,Ubuntu,Cantarell,Fira Sans,Droid Sans,Helvetica Neue,sans-serif;font-size:13px;line-height:1.4;padding:2% 7%">

              <img id="Qwaiting" src="' .$logo. '" alt="logo" class="CToWUd" style="vertical-align:middle;" width="100">

        <div style="background:#fff;border-top-color:#6e8cce;border-top-style:solid;border-top-width:4px;margin:25px auto;
        border-radius: 8px;">
            <div style="border-color:#e5e5e5;border-style:none solid solid;border-width:2px;padding:7%">
            <div>



        </div>

            '.$templateBody.'

            '. $actionButtons .'

            </div>
        </div>
        <div style="text-align:center" align="center">
            <p style="color:#999;font-size:11px;line-height:1.4;margin:5px 0">Copyright ' .date("Y"). ' © Qwaiting Inc. All Rights Reserved.</p>
        </div>
        </div>';



        try {


            // Send the email using the specified template and SMTP settings


            \Log::info('first check: ' . filter_var($data['to_mail'], FILTER_VALIDATE_EMAIL));

            if (!empty($data['to_mail']) && filter_var($data['to_mail'], FILTER_VALIDATE_EMAIL)) {


              if($enableSlack) {
                  try {
                    $slack = new SlackService($teamId, $data['locations_id']);
                    $slackResponse = $slack->sendSms(
                        $data['to_mail'],
                        $slacktemplateBody
                    );

                    \Log::info("Slack notification sent: " . json_encode($slackResponse));

                    if (!empty($logData)) {
                        $logData['channel'] = 'slack';
                        $logData['message'] = $slacktemplateBody ?? '';
                        $logData['event_name'] = $title;

                        // ✅ Check if failed
                        if (isset($slackResponse['status']) && $slackResponse['status'] === 'failed') {
                            $logData['status'] = MessageDetail::FAILED_STATUS;
                            $logData['failed_reason'] = $slackResponse['reason'] ?? 'Unknown Slack error';
                        } else {
                            $logData['status'] = MessageDetail::SENT_STATUS;
                            $logData['response_status'] = json_encode($slackResponse);
                        }

                        MessageDetail::storeLog($logData);
                    }
                } catch (\Throwable $ex) {
                    \Log::error("Slack DM failed: " . $ex->getMessage());

                    if (!empty($logData)) {
                        $logData['channel'] = 'slack';
                        $logData['message'] = $slacktemplateBody ?? '';
                        $logData['event_name'] = $title;
                        $logData['status'] = MessageDetail::FAILED_STATUS;
                        $logData['failed_reason'] = $ex->getMessage();
                        MessageDetail::storeLog($logData);
                    }
                }

              } // ✅ Also send via Slack (optional based on event)

                 $logData['failed_reason'] = '';
                 $logData['response_status'] = '';
                \Log::info('second check: ' . $details->hostname.','.$details->encryption.','.$details->username.','.$details->password.','.$details->from_email.','.$details->from_name);
                if(!empty($details->hostname) && !empty($details->port) &&  !empty($details->username) && !empty($details->password) && !empty($details->from_email) &&  !empty($details->from_name)){
                    Config::set('mail.mailers.smtp.transport', 'smtp');
                    Config::set('mail.mailers.smtp.host', trim($details->hostname));
                    Config::set('mail.mailers.smtp.port', trim($details->port));
                    Config::set('mail.mailers.smtp.encryption', trim($details->encryption ?? 'ssl'));
                    Config::set('mail.mailers.smtp.username', trim($details->username));
                    Config::set('mail.mailers.smtp.password', trim($details->password));

                    Config::set('mail.from.address', trim($details->from_email));
                    Config::set('mail.from.name', trim($details->from_name));

                    \Log::info('send mail check');
                    Mail::html( $templateContent, function ($message) use ($data, $title, $smtpDetails) {
                        $message->from($smtpDetails['from_email'], $smtpDetails['from_name']);
                        $message->to($data['to_mail'])->subject($title);

                    });


                    if(!empty($logData)){
                       $logData['channel'] = 'email';
                        $logData['message'] = $templateBody ?? '';
                        $logData['event_name'] = $title;
                        $logData['status'] = MessageDetail::SENT_STATUS;
                        MessageDetail::storeLog($logData);
                    }
                }


        }

        } catch (\Throwable $e) {
            // Log the error and return the message
            \Log::error('Error sending email: ' . $e->getMessage());
             if(!empty($logData)){
                 $logData['channel'] = 'email';
                    $logData['message'] = $templateBody ?? '';
                    $logData['event_name'] = $title;
                    $logData['status'] = MessageDetail::FAILED_STATUS;
                    $logData['failed_reason'] = $e->getMessage();
                    MessageDetail::storeLog($logData);
             }
            // return $e->getMessage();
        }
    }


    /**
 * Replace placeholders in the email template with actual data.
 *
 * @param string $template The email template content.
 * @param array $data The data containing placeholder values.
 * @return string The processed template with replaced values.
 */
public static function replaceTemplatePlaceholders($template, $data,$teamId)
{

  $team=Tenant::where('id',$teamId)->first();
  $data['panel_name']=isset($team['name']) != null ? $team['name'] : '';
  $data['name']=$data['name'] ?? '';
  $data['service_name']=$data['category_name'] ?? '';
  $data['service_1']=$data['category_name'] ?? '';
  $data['service_2']=$data['secondC_name'] ?? '';
  $data['service_3']=$data['thirdC_name'] ?? '';
  $data['queue_count']=$data['pending_count'] ?? '';
     if(Auth::check()){
        $data['staff_name'] = Auth::user()->name;
    }else{
        $data['staff_name'] ='';
    }

    // Define placeholders and their corresponding replacements
    $placeholders = [
        '{{customer_name}}',
        '{{service_name}}',
        '{{panel_name}}',
        '{{token}}',
        '{{waiting_time}}',
        '{{queue_count}}',
        '{{ticket_link}}',
        '{{pax}}',
        '{{counter_name}}',
        '{{feedback_link}}',
        '{{service_1}}',
        '{{service_2}}',
        '{{service_3}}',
        '{{booking_date}}',
        '{{booking_time}}',
        '{{generate_queue_link}}',
        '{{booking_id}}',
        '{{booking_link}}',
        '{{service_time}}',
        '{{service_note}}',
        '{{staff_name}}',
        '{{meeting_link}}',
        '{{ user_name }}',
        '{{ user_email }}',
        '{{ user_id }}',
        '{{ amount }}',
        '{{ payment_id }}',
        '{{ payment_date }}',
        '{{ wallet_balance }}',
        '{{ app_name }}',
    ];

    $replacements = [
        $data['name'] ?? '',              // {{customer_name}}
        $data['service_name'] ?? '',     // {{category_name}}
        $data['panel_name'] ?? '',        // {{panel_name}}
        $data['token'] ?? '',             // {{token}}
        $data['waiting_time'] ?? '',      // {{waiting_time}}
        $data['queue_count'] ?? '',       // {{queue_count}}
        $data['ticket_link'] ?? '',       // {{ticket_link}}
        $data['pax'] ?? '',               // {{pax}}
        $data['counter_name'] ?? '',      // {{counter_name}}
        $data['feedback_link'] ?? '',     // {{feedback_link}}
        $data['service_1'] ?? '',        // {{service_1}}
        $data['service_2'] ?? '',        // {{service_2}}
        $data['service_3'] ?? '',        // {{category_3}}
        $data['booking_date'] ?? '',      // {{booking_date}}
        $data['booking_time'] ?? '',      // {{booking_time}}
        $data['generate_queue_link'] ?? '', // {{generate_queue_link}}
        $data['refID'] ?? '',        // {{booking_id}}
        $data['booking_link'] ?? '',      // {{booking_link}}
        $data['service_time'] ?? '',      
        $data['service_note'] ?? '',      
        $data['staff_name'] ?? '',
        $data['meeting_link'] ?? '',
        $data['user_name'] ?? '',
        $data['user_email'] ?? '',
        $data['user_id'] ?? '',
        $data['amount'] ?? '',
        $data['payment_id'] ?? '',
        $data['payment_date'] ?? '',
        $data['wallet_balance'] ?? '',
        $data['app_name'] ?? $data['panel_name'] ?? '',
    ];


    // Replace all placeholders in the template
    return str_replace($placeholders, $replacements, $template);
}



}
