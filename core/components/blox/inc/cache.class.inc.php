<?php   

class xetCache {
        // Gibt an, ob die Dateinamen mit sha1 gehasht werden  sollen, oder nicht
        var $bHashFilenamesSHA1 = true;

        // Die Lebenszeit in Sekunden (1800 Sekunden = 30 Minuten)
        var $iDefaultLifetime = 30000000;

        // Der Pfad zu den Cache-Dateien (absolut oder relativ)
        var $sCachePath = './cache/';

        // Die Dateierweiterung f�r die Cache-Dateien (z.B. ".cache")
        var $sFileExtension = '.cache';

        /**
         * Konstruktor der Klasse
         *
         */
        function xetCache($xetconfig) {

        	$this->xetconfig=$xetconfig;
                // Wandelt den eingegebenen Pfad in einen absoluten Pfad um
                //$this -> sCachePath = realpath($this -> sCachePath);
        } 

        /**
         * S�ubert die Cache-Daten
         *     - Sucht nach alten, abgelaufenen Cache-Daten und entfernt diese
         *     - Gibt die Anzahl der gel�schten Cache-Eintr�ge zur�ck
         *
         * @return integer
         */
        function cleanUpCache() {
                // Suche nach allen Dateien, die zum Cache geh�ren
                $aFiles = glob($this -> sCachePath . DIRECTORY_SEPARATOR . '*' . $this -> sFileExtension);

                // Falls keine Dateien verf�bar sind 
                if(count($aFiles) < 1) {
                        // gib 0 zur�ck (0 Dateien wurden entfernt)
                        return 0;
                }

                $iCounter = 0;
                // Nun, es gibt mindestens eine Datei ...
                foreach($aFiles as $sFileName) {
                        // Datei versuchen zu �ffnen ..
                        $rHandle = @fopen($sFileName, 'r');
                        if(is_resource($rHandle) === false) {
                                continue;
                        }

                        // Da die Verbindung nun steht, wird diese Datei
                        // tempo�r gesperrt
                        flock($rHandle, LOCK_SH);

                        // Lesen wir die Daten ein ...
                        $sData = '';
                        while(!feof($rHandle)) {
                                $sData .= fread($rHandle, 4096);
                        }

                        $mData = @unserialize($sData);
                        if(!$mData) {
                                // Es ist wohl etwas schief gelaufen,
                                // weiter mit der n�chsten Datei
                                continue;
                        }

                        if(time() + $this -> iDefaultLifetime > $mData[0]) {
                                // Die Lebenszeit ist abgelaufen,
                                // also wird dieser Cache-Eintrag gel�scht
                                fclose($rHandle);
                                if($this -> deleteCache($sFileName, true) === true) {
                                        $iCounter ++;
                                }
                        }

                        else {
                                // Die Datei ist noch g�ltig,
                                // weiter mit der n�chsten Datei
                                fclose($rHandle);
                        }

                }

                // Jetzt nur noch die Anzahl der gel�schten Dateien
                // zur�ckgeben, fertig.
                return $iCounter;
        } 

        /**
         * L�scht einen existierenden Cache-Eintrag
         *
         * @param string $sCacheName
         * @param boolean $bIsFileName
         * @return boolean
         */
        function deleteCache($sCacheName, $bIsFileName = false) {
                // Falls der �bergebene Wert kein Dateiname ist,
                // muss dieser erst generiert werden
                $result='';
				if($bIsFileName === false) {
                        //$sFileName = $this -> getFileName($sCacheName);
						$sFileName = $this -> makeFileName($sCacheName);
                }

                else {
                        // Ansonsten kann der Name 1:1 �bernommen werden
                        $sFileName = $sCacheName;
                }
                
                // L�scht die Datei und gibt bei Erfolg "true",
                // bei Misserfolg "false" zur�ck.
				if ( $this->readCache($sFileName,true) !== false) {
					$result=@unlink($sFileName);
				}
				
                return $result;
        } 

        /**
         * Gibt den Dateinamen inklusive Pfadangabe zur�ck
         * Auf Wunsch wird dieser Dateiname mittels der Funktion sha1() gehasht.
         *
         * @param string $sCacheName
         * @return string
         */
        function makeFileName($sCacheName) {
                //$sFileIdentifier = ($this -> bHashFilenamesSHA1 === true) ? sha1($sCacheName) : $sCacheName;
                return $modx->config['base_path'].$this->xetconfig['cachepath'].'/'.$this->xetconfig['projectname'].'.'.$this->xetconfig['task'].'.'.$sCacheName;
        } 


        /**
         * Gibt den Dateinamen inklusive Pfadangabe zur�ck
         * Auf Wunsch wird dieser Dateiname mittels der Funktion sha1() gehasht.
         *
         * @param string $sCacheName
         * @return string
         */
        function getFileName($sCacheName) {
                $sFileIdentifier = ($this -> bHashFilenamesSHA1 === true) ? sha1($sCacheName) : $sCacheName;
                return $this -> sCachePath . DIRECTORY_SEPARATOR . $sFileIdentifier . $this -> sFileExtension;
        } 

        /** 
         * Liest die Daten aus dem Cache, falls diese existieren
         * und noch g�ltig sind.
         *
         * @param string $sCacheName
         * @return boolean (if not succeeded) | mixed (if succeeded)
         */
        function readCache($sCacheName , $bIsFileName = false) {
                // Falls der �bergebene Wert kein Dateiname ist,
                // muss dieser erst generiert werden
               
				if($bIsFileName === false) {
                        //$sFileName = $this -> getFileName($sCacheName);
						$sFileName = $this -> makeFileName($sCacheName);
                }

                else {
                        // Ansonsten kann der Name 1:1 �bernommen werden
                        $sFileName = $sCacheName;
                }
                // Leere Cache-Eintrag-Namen sind nicht erlaubt :-)
                if(trim($sCacheName) == '') {
                        return false;
                }

                // Der Cache-Name ist also nicht leer, nun wird
                // der Dateiname inklusive Pfad generiert
                //$sFileName = $this -> getFileName($sCacheName);
                //$sFileName = $sCacheName;
				
				
                // Nun m�ssen wir pr�fen, ob die Datei �berhaupt
                // existiert.. Wenn nicht, wird die Funktion abgebrochen
                // und false zur�ckgegeben.
                if(file_exists($sFileName) === false) {
                        return false;
                }

                // Hier m�ssen wir pr�fen, ob die Datei lesbar ist.
                // Wenn nicht, wird die Funktion ebenfalls abgebrochen
                // und false zur�ckgegeben.
                if(is_readable($sFileName) === false) {
                        return false;
                } 
                $rHandle = @fopen($sFileName, 'r');
                if(is_resource($rHandle) === false) {
                        // Falls troztdem etwas schiefgegangen ist,
                        // gib wieder false zur�ck
                        return false;
                }

                // Jetzt haben wir die Datei ge�ffnet, nun sperren wir sie
                flock($rHandle, LOCK_SH);

                // Nun lesen wir die Daten aus ..
                $sData = '';
                while(!feof($rHandle)) {
                        $sData .= fread($rHandle, 4096);
                }

                // und schlie�en diese Datei wieder (wichtig!).
                fclose($rHandle); 
                // Nun ent-serialisieren wir die gegebenen Daten
                $mData = @unserialize($sData);

                // Falls beim ent-serialisieren etwas schiefgelaufen ist,
                // oder der aktuelle Zeitstempel bereits gr��er als der
                // im Cache ist (d.h. der Cache ist verfallen) wird
                // die Datei gel�scht, und es wird false zur�ckgegeben.
                if(!$mData or time() > $mData[0]) {
                        // Delete that file and return false
                        $this -> deleteCache($sCacheName);
                        return false;
                } 
                return $mData[1];
				
        } 												
        /**
         * Diese Funktion l�scht jedliche Cache-Eintr�ge, die zu finden sind 
         * und nimmt dabei keine R�cksicht auf die Verfallsdaten
         * der jeweiligen Dateien.
         *
         * @return integer
         */
        function clearCache($sFileName) {
                // Suche nach allen Dateien, die zum Cache geh�ren
				//$sFileName = $this -> makeFileName('*');
			
                $aFiles = glob($sFileName);

                // Falls keine Dateien verf�bar sind ..
                if(count($aFiles) < 1) {
                        // .. geben wir 0 zur�ck (integer), denn die Funktion
                        // gibt die Anzahl der gel�schten Dateien zur�ck
                        return 0;
                }

                // gib 0 zur�ck (0 Dateien wurden entfernt)
                $iCounter = 0;

                // Nun, es gibt mindestens eine Datei ...
                foreach($aFiles as $sFileName) {
                	   
                        // Jetzt pr�fen wir nicht, ob der Eintrag g�ltig ist
                        // oder nicht - sondern l�schen ihn einfach :-)
                        if($this -> deleteCache($sFileName, true) === true) {
                                $iCounter ++;
                        }

                }

                // Zuletzt geben wie die Anzahl der gel�schten
                // Dateien zur�ck
                return $iCounter;
        } 

        /**
         * Diese Funktion l�scht jedliche Cache-Eintr�ge, die zu finden sind 
         * und nimmt dabei keine R�cksicht auf die Verfallsdaten
         * der jeweiligen Dateien.
         *
         * @return integer
         */
        function truncateCache() {
                // Suche nach allen Dateien, die zum Cache geh�ren
                $aFiles = glob($this -> sCachePath . DIRECTORY_SEPARATOR . '*' . $this -> sFileExtension);

                // Falls keine Dateien verf�bar sind ..
                if(count($aFiles) < 1) {
                        // .. geben wir 0 zur�ck (integer), denn die Funktion
                        // gibt die Anzahl der gel�schten Dateien zur�ck
                        return 0;
                }

                // gib 0 zur�ck (0 Dateien wurden entfernt)
                $iCounter = 0;

                // Nun, es gibt mindestens eine Datei ...
                foreach($aFiles as $sFileName) {
                        // Jetzt pr�fen wir nicht, ob der Eintrag g�ltig ist
                        // oder nicht - sondern l�schen ihn einfach :-)
                        if($this -> deleteCache($sFileName, true) === true) {
                                $iCounter ++;
                        }

                }

                // Zuletzt geben wie die Anzahl der gel�schten
                // Dateien zur�ck
                return $iCounter;
        } 

        /**
         * Schreibt die �bergebenen Daten in den Cache
         *
         * @param string $sCacheName
         * @param mixed $mData
         * @param integer $iLifetime ( in seconds )
         * @return boolean
         */
        function writeCache($sCacheName, $mData, $iLifetime = -1) { 
                if(is_int($iLifetime) === false or $iLifetime < 0) {
                        // Falls der �bergebene Lebensdauer-Wert keine Zahl
                        // ist oder kleiner Null, wird der standardm��ige Wert
                        // genommen
                        $iLifetime = $this -> iDefaultLifetime;
                }

                // Hier wird wieder der Dateiname zusammengebaut
                //$sFileName = $this -> getFileName($sCacheName);
				//$sFileName = $sCacheName;
				$sFileName = $this -> makeFileName($sCacheName);
                // Wir versuche die Datei zu �ffnen
                $rHandle = @fopen($sFileName, 'a');

                // Falls dies nicht gelungen ist, geben wir false zur�ck
                if(is_resource($rHandle) === false) {
                	echo 'cache-error no write-permissions for '.$sFileName;
                        return false;
                }

                // Danach sperren wir die Datei, um eventuelle
                // race-conditions zu vermeiden
                flock($rHandle, LOCK_EX);

                // Nun leeren wir die Datei
                ftruncate($rHandle, 0); 
                $sSerializedData = serialize(array( (time() + $iLifetime), $mData)); 								
                // Nun schreiben wir die neuen Cache-Daten
                // (oder versuchen es zumindest)
                if(@fwrite($rHandle, $sSerializedData) === false) {
                        // Sollte hier an dieser Stelle ein Fehler auftreten,
                        // wird false zur�ckgegeben
                        return false;
                }

                fclose($rHandle);
                return true;
        } 
		
}
?>