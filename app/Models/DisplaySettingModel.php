<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class DisplaySettingModel extends Model
 {
    use HasFactory;


    protected $table ='display_settings';
    protected $fillable = [ 'team_id', 'location_id','screen_tune', 'created_by', 'created_at', 'updated_at' ];

    const SERVICE_OPEN = 'open';
    const SERVICE_CLOSE = 'closed';
    const DEFAULT_SETTING_TUNE = 0;
    const DEFAULT_EN_LANG = 'en-US';

    public static function getDetails($teamId, $location = null) {
        $query = self::where('team_id', $teamId);

        if (!is_null($location)) {
            $query->where('location_id', $location);
        }

        return $query->first();
    }

    public static function getVoiceMessages($key = null)
{
    $messages = [
        10 => __('Vietnamese'),
        9 => __('text.Voice Message in French Language Only'),
        8 => __('text.Voice Message in Spanish Language Only'),
        7 => __('text.Arabic & English Voice Message'),
        6 => __('text.Voice Message in Arabic Language Only'),
        5 => __('text.Voice Message With Spanish Language Also'),
        4 => __('text.Voice Message With Bangladesh Language Also'),
        3 => __('text.Voice Message With Arabic Language Also'),
        2 => __('text.Voice Message With Chinese Language Also'),
        1 => __('text.Voice message in English'),
        0 => __('text.Ding Dong'),
    ];

    if ($key !== null && array_key_exists($key, $messages)) {
        return $messages[$key];
    }

    return $messages;
}

    public static function getDisplayVoices(){
        return [
            10 => ['lang' => 'vi-VN', 'voice' => "Vietnamese Female", "dual" => false],
            9  => ['lang' => 'fr-FR', 'voice' => "Google français", "dual" => false],
            8  => ['lang' => 'es-ES', 'voice' => "Google español", "dual" => false],
            7  => ['lang' => 'ar-SA', 'voice' => "Google العربية", "dual" => true],
            6  => ['lang' => 'ar-SA', 'voice' => "Google العربية", "dual" => false],
            5  => ['lang' => 'es-ES', 'voice' => "Google español", "dual" => true],
            4  => ['lang' => 'bn-IN', 'voice' => "Google বাংলা", "dual" => true],
            3  => ['lang' => 'ar-SA', 'voice' => "Google العربية", "dual" => true],
            2  => ['lang' => 'zh-CN', 'voice' => "Google 粤語（香港）", "dual" => true],
            1  => ['lang' => 'en-US', 'voice' => "Google US English", "dual" => false],
            0  => ['lang' => null, 'voice' => null, "dual" => false]
        ];

    }

    public static function getVoiceChosen($displayTune) {
       $voiceMessages = self::getDisplayVoices();
       return $voiceMessages[$displayTune] ?? ['lang' => 'en-US', 'voice' => 'Google US English'];
    }

}
