<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    // Activity types constants
    const LOGIN = 'login';
    const LOGOUT = 'logout';
    const PASSWORD_RESET = 'password_reset';
    const PASSWORD_CHANGE = 'password_change';
    const FAILED_LOGIN = 'failed_login';

    /**
     * Log an activity
     *
     * @param int $userId
     * @param string $activityType
     * @param string $description
     * @param array $additionalData
     * @return ActivityLog
     */
    public static function log($userId, $activityType, $description, $additionalData = [])
    {
        return ActivityLog::create([
            'user_id' => $userId,
            'activity_type' => $activityType,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'additional_data' => $additionalData
        ]);
    }

    /**
     * Log a login activity
     *
     * @param int $userId
     * @param bool $success
     * @return ActivityLog
     */
    public static function logLogin($userId, $success = true)
    {
        $activityType = $success ? self::LOGIN : self::FAILED_LOGIN;
        $description = $success ? 'User logged in successfully' : 'Failed login attempt';

        return self::log($userId, $activityType, $description);
    }

    /**
     * Log a logout activity
     *
     * @param int $userId
     * @return ActivityLog
     */
    public static function logLogout($userId)
    {
        return self::log($userId, self::LOGOUT, 'User logged out');
    }

    /**
     * Log a password reset activity
     *
     * @param int $userId
     * @param string $method
     * @return ActivityLog
     */
    public static function logPasswordReset($userId, $method = 'email')
    {
        return self::log(
            $userId,
            self::PASSWORD_RESET,
            'Password was reset via ' . $method,
            ['method' => $method]
        );
    }

    /**
     * Log a password change activity
     *
     * @param int $userId
     * @return ActivityLog
     */
    public static function logPasswordChange($userId)
    {
        return self::log(
            $userId,
            self::PASSWORD_CHANGE,
            'Password was changed by user'
        );
    }
}