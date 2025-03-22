<?php
namespace App\Logger;

use App\Enum\LogAction;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;

class CustomLogger
{
    private array $loggers = [];
    private string $logDirectory;

    public function __construct(string $logDirectory)
    {
        $this->logDirectory = $logDirectory;
    }

    private function getLogger(LogAction $action): MonologLogger
{
    $category = strtolower($action->getCategory());

    // Prüfen ob ein Logger für die Kategorie bereits existiert
    if (!isset($this->loggers[$category])) {
        $logger = new MonologLogger($category);

        // Wir setzen unsere Zeitzone fest, da sonst UTC Standard
        $logger->setTimezone(new \DateTimeZone('Europe/Berlin'));

        // Den Ordner mit der Category erstellen
        $categoryDirectory = $this->logDirectory . '/' . $category;
        if (!is_dir($categoryDirectory)) {
            mkdir($categoryDirectory, 0777, true);
        }

        // Aktuelles Datum im Format JahrMonatTag
        $currentDate = date('Ymd');

        // Erstelle den Pfad zur Datei mit dem Datumsstempel
        $logFilePath = $categoryDirectory . '/events_' . $currentDate . '.log';
        
        // Füge den StreamHandler hinzu, der die Logdatei verarbeitet
        $logger->pushHandler(new StreamHandler($logFilePath, $action->getLogLevel()));
        
        // Speichere den Logger im Logger Array
        $this->loggers[$category] = $logger;
    }

    // Returnen dann eine Instanz des Loggers und der jeweiligen Category Datei
    return $this->loggers[$category];
}

    public function logEvent(LogAction $action, $data): void
    {
        $logger = $this->getLogger($action);
        // Wir geben erstmal das Log Level und die Kategorie aus
        $logger->log($action->getLogLevel(), $action->getMessage());   
      
        // Wir geben aus um wen es sich dabei handelt
        $userIdentity = [
            "ip_adress" => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            "user" => isset($data['user_login']) ? $data['user_login']['user_email'] : 'unknown_user', // Hier kommt noch Abfrage auf eingeloggte User!!
        ];
        $logger->log($action->getLogLevel(), json_encode($userIdentity));      

        // Und nun erzeugen wir die Daten Message darunter
        $logger->log($action->getLogLevel(), json_encode(['data' => $data]));
    }    
}