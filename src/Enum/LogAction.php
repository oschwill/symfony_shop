<?php
namespace App\Enum;

enum LogAction: string
{
     // Produkt Ereignisse
    case PRODUCT_GETALL = 'PRODUCT_GETALL';
    case PRODUCT_GETONE = 'PRODUCT_GETONE';
    case PRODUCT_INSERTONE = 'PRODUCT_INSERTONE';
    case PRODUCT_UPDATEONE = 'PRODUCT_UPDATEONE';
    case PRODUCT_DELETEONE = 'PRODUCT_DELETEONE';
    
    // Benutzer Ereignisse
    case USER_REGISTER = 'USER_REGISTER';
    case USER_LOGIN = 'USER_LOGIN';
    case USER_UPDATE = 'USER_UPDATE';
    
    // E-Mail Ereignisse
    case EMAIL_SENT = 'EMAIL_SENT';
    
    // Fehler
    case CRITICAL_ERROR = 'CRITICAL_ERROR';
    case USER_LOGIN_ERROR = 'USER_LOGIN_ERROR';

    // Log Message returnen
    public function getMessage(): string
    {
        return match ($this) {
            self::PRODUCT_GETALL => '<<<*** GET ALL PRODUCTS ***>>>',
            self::PRODUCT_GETONE => '<<<*** GET SINGLE PRODUCT ***>>>',
            self::PRODUCT_INSERTONE => '<<<*** INSERT PRODUCT ***>>>',
            self::PRODUCT_UPDATEONE => '<<<*** UPDATE PRODUCT ***>>>',
            self::PRODUCT_DELETEONE => '<<<*** DELETE PRODUCT ***>>>',
            self::USER_REGISTER => '<<<*** USER REGISTERED ***>>>',
            self::USER_LOGIN => '<<<*** USER LOGGED IN ***>>>',
            self::USER_UPDATE => '<<<*** UPDATE USER ***>>>',
            self::EMAIL_SENT => '<<<*** EMAIL SENT ***>>>',
            self::CRITICAL_ERROR => '<<<*** CRITICAL ERROR ***>>>',
            self::USER_LOGIN_ERROR => '<<<*** USER LOGIN ERROR ***>>>',
        };
    }

    // Holt die Kategorie des Logevents anhand der LogAction Ereignisse
    public function getCategory(): string
    {
        return match ($this) {
            self::PRODUCT_GETALL, self::PRODUCT_GETONE, self::PRODUCT_INSERTONE, self::PRODUCT_UPDATEONE, self::PRODUCT_DELETEONE => 'product',
            self::USER_REGISTER, self::USER_UPDATE, self::USER_LOGIN => 'user',
            self::EMAIL_SENT => 'email',
            self::CRITICAL_ERROR, self::USER_LOGIN_ERROR => 'error',
        };
    }

    // Hier holen wir uns das LogLevel, Debug oder Error anhand der LogAction Ereignisse, Critical Error loggt erstmal allgemein jeden Fehler
    public function getLogLevel(): int
    {
        return match ($this) {
            self::PRODUCT_GETALL, self::PRODUCT_GETONE, self::PRODUCT_INSERTONE, self::PRODUCT_UPDATEONE, self::PRODUCT_DELETEONE => \Monolog\Logger::DEBUG,
            self::USER_REGISTER, self::USER_UPDATE, self::USER_LOGIN, self::EMAIL_SENT => \Monolog\Logger::DEBUG,
            self::CRITICAL_ERROR, self::USER_LOGIN_ERROR => \Monolog\Logger::ERROR,
        };
    }
}