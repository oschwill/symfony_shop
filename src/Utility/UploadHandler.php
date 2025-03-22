<?php
namespace App\Utility;

use App\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Ramsey\Uuid\Uuid;

class UploadHandler
{

    public function __construct(private string $uploadDir)
    {}

    public function handleProfilePictureUpload(User $user, ?UploadedFile $picture = null, ?Array &$changes = null): void
    {
        if ($picture) {

            // Erstelle den Verzeichnispfad mit UUIDs
            $uuid = Uuid::uuid4()->toString();
            $userDirectory = $this->uploadDir . "/Users" . '/' . $uuid;

            // Wenn das Verzeichnis also nicht existiert erzeugen wir es
            if (!is_dir($userDirectory)) {
                mkdir($userDirectory, 0755, true);
            }

            // Verschiebe das Bild in den Ordner und speichere den Pfad in die DB
            $newFilename = uniqid() . '.' . $picture->guessExtension();
            $picture->move($userDirectory, $newFilename);
            $finalPath = 'uploads/Users/' . $uuid . '/' . $newFilename;
            $user->setPicturePath($finalPath);

            if ($changes !== null) {
              $changes['picturePath'] = $finalPath;
            }
        }
    }

    private function removeEmptyDirectory(string $directory): void
    {
        if (is_dir($directory) && !(new \FilesystemIterator($directory))->valid()) {
            if (!rmdir($directory)) {
                throw new \Exception('Failed to remove directory: ' . $directory);
            }
        }
    }

    public function removeUploadedFile(User $user): bool{
        $oldPicturePath = $user->getPicturePath();
            
        // Sicherstellen, dass das alte Bild existiert und gelöscht werden kann

        if ($oldPicturePath && file_exists($oldPicturePath)) {
            // Lösche das Bild
            if (unlink($oldPicturePath)) {
                // Lösche den übergeordneten Ordner, wenn er leer ist
                $directory = dirname($oldPicturePath);
                $this->removeEmptyDirectory($directory);

                return true;
            } else {                    
                return false;                    
            }
        }else{
            // Wenn nicht existiert dann haben wir auch nix zum löschen also true returnen
            return true;
        }
    }
}