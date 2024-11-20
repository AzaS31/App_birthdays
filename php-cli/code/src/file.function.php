<?php

// function readAllFunction(string $address) : string {
function readAllFunction(array $config) : string {
    $address = $config['storage']['address'];
    
    if (file_exists($address) && is_readable($address)) {
        $file = fopen($address, "rb");
        
        $contents = ''; 
    
        while (!feof($file)) {
            $contents .= fread($file, 100);
        }
        
        fclose($file);
        return $contents;
    }
    else {
        return handleError("Файл не существует");
    }
}

// function addFunction(string $address) : string {
function addFunction(array $config) : string {
    $address = $config['storage']['address'];

    $name = readline("Введите имя: ");
    $date = readline("Введите дату рождения в формате ДД-ММ-ГГГГ: ");
    if (!validateDate($date)) {
        return handleError("Некорректный формат даты");
    }
    $data = $name . ", " . $date . "\r\n";

    $fileHandler = fopen($address, 'a');

    if(fwrite($fileHandler, $data)){
        return "Запись $data добавлена в файл $address"; 
    }
    else {
        return handleError("Произошла ошибка записи. Данные не сохранены");
    }

    fclose($fileHandler);
}

// function clearFunction(string $address) : string {
function clearFunction(array $config) : string {
    $address = $config['storage']['address'];

    if (file_exists($address) && is_readable($address)) {
        $file = fopen($address, "w");
        
        fwrite($file, '');
        
        fclose($file);
        return "Файл очищен";
    }
    else {
        return handleError("Файл не существует");
    }
}

function helpFunction() {
    return handleHelp();
}

function readConfig(string $configAddress): array|false{
    return parse_ini_file($configAddress, true);
}

function readProfilesDirectory(array $config): string {
    $profilesDirectoryAddress = $config['profiles']['address'];

    if(!is_dir($profilesDirectoryAddress)){
        mkdir($profilesDirectoryAddress);
    }

    $files = scandir($profilesDirectoryAddress);

    $result = "";

    if(count($files) > 2){
        foreach($files as $file){
            if(in_array($file, ['.', '..']))
                continue;
            
            $result .= $file . "\r\n";
        }
    }
    else {
        $result .= "Директория пуста \r\n";
    }

    return $result;
}

function readProfile(array $config): string {
    $profilesDirectoryAddress = $config['profiles']['address'];

    if(!isset($_SERVER['argv'][2])){
        return handleError("Не указан файл профиля");
    }

    $profileFileName = $profilesDirectoryAddress . $_SERVER['argv'][2] . ".json";

    if(!file_exists($profileFileName)){
        return handleError("Файл $profileFileName не существует");
    }

    $contentJson = file_get_contents($profileFileName);
    $contentArray = json_decode($contentJson, true);

    $info = "Имя: " . $contentArray['name'] . "\r\n";
    $info .= "Фамилия: " . $contentArray['lastname'] . "\r\n";

    return $info;
}

function getDayEnding($number) {
    $lastDigit = abs($number) % 10;
    $lastTwoDigits = abs($number) % 100;

    if ($lastDigit == 1 && $lastTwoDigits != 11) {
        return "день";
    } elseif (($lastDigit == 2 || $lastDigit == 3 || $lastDigit == 4) && ($lastTwoDigits < 10 || $lastTwoDigits >= 20)) {
        return "дня";
    } else {
        return "дней";
    }
}

function findBirthdays(array $config): string {
    $address = $config['storage']['address'];
    $today = date('d-m');

    if (!file_exists($address) || !is_readable($address)) {
        return handleError("Файл с данными не существует или недоступен");
    }

    $file = fopen($address, "r");
    $birthdays = [];
    $nearestBirthday = null;
    $nearestDaysDiff = PHP_INT_MAX;

    while (($line = fgets($file)) !== false) {
        $line = trim($line);
        if (!$line) {
            continue;
        }

        [$name, $birthDate] = explode(", ", $line);
        [$day, $month, $year] = explode("-", $birthDate);

        if ("$day-$month" === $today) {
            $birthdays[] = $name;
        } else {
            $birthdayThisYear = strtotime("$day-$month-" . date('Y'));
            $now = time();
            $daysDiff = ceil(($birthdayThisYear - $now) / 86400);

            if ($daysDiff < 0) {
                $birthdayNextYear = strtotime("$day-$month-" . (date('Y') + 1));
                $daysDiff = ceil(($birthdayNextYear - $now) / 86400);
            }

            if ($daysDiff < $nearestDaysDiff) {
                $nearestDaysDiff = $daysDiff;
                $nearestBirthday = "$name ($day-$month)";
            }
        }
    }

    fclose($file);

    if ($birthdays) {
        return "Сегодня день рождения у: " . implode(", ", $birthdays);
    }

    if ($nearestBirthday) {
        $dayEnding = getDayEnding($nearestDaysDiff);
        return "Ближайший день рождения у: $nearestBirthday через $nearestDaysDiff $dayEnding";
    }

    return "Дни рождения не найдены.";
}


function deleteRecord(array $config): string {
    $address = $config['storage']['address'];

    $nameToDelete = readline("Введите имя для удаления: ");
    
    if (!file_exists($address) || !is_readable($address)) {
        return handleError("Файл с данными не существует или недоступен");
    }

    $file = fopen($address, "r");
    $lines = [];
    $found = false;

    while (($line = fgets($file)) !== false) {
        $line = trim($line);
        if ($line) {
            [$name, $birthDate] = explode(", ", $line);
            if (strtolower($name) !== strtolower($nameToDelete)) {
                $lines[] = $line;
            } else {
                $found = true; 
            }
        }
    }

    fclose($file);

    if ($found) {
        $file = fopen($address, "w");
        foreach ($lines as $line) {
            fwrite($file, $line . "\r\n");
        }
        fclose($file);
        return "Запись с именем '$nameToDelete' была удалена.";
    } else {
        return handleError("Запись с именем '$nameToDelete' не найдена.");
    }
}
