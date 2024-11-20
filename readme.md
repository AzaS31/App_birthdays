# Домашняя работа
## Задание 1
1. `Обработка ошибок. Посмотрите на реализацию функции в файле fwrite-cli.php в исходниках. Может ли пользователь ввести некорректную информацию (например, дату в виде 12-50-1548)? Какие еще некорректные данные могут быть введены? Исправьте это, добавив соответствующие обработки ошибок.`
```
function validateDate(string $date): bool {
    $dateBlocks = explode("-", $date);

    if (count($dateBlocks) !== 3) {
        return false;
    }

    [$day, $month, $year] = $dateBlocks;

    if (!ctype_digit($day) || !ctype_digit($month) || !ctype_digit($year)) { //Эта проверка убедится, что каждая часть (день, месяц, год) состоит только из цифр
        return false;
    }

    $day = (int)$day;
    $month = (int)$month;
    $year = (int)$year;

    if ($year > (int)date('Y')) { //Проверяется, что год не больше текущего
        return false;
    }

    if (!checkdate($month, $day, $year)) { //проверяет, является ли данная дата реальной
        return false;
    }

    return true;
}
```

2. `Поиск по файлу. Когда мы научились сохранять в файле данные, нам может быть интересно не только чтение, но и поиск по нему. Например, нам надо проверить, кого нужно поздравить сегодня с днем рождения среди пользователей, хранящихся в формате:`
```
//Функция для коректного вывода окончании слова дней
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
```

3. ` Удаление строки. Когда мы научились искать, надо научиться удалять конкретную строку. Запросите у пользователя имя или дату для удаляемой строки. После ввода либо удалите строку, оповестив пользователя, либо сообщите о том, что строка не найдена.`

```
// Удаляем по имени
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

```

4. ` Добавьте новые функции в итоговое приложение работы с файловым хранилищем.`

Добавили в функцию parseCommand() в main.function.php
```
function parseCommand() : string {
    $functionName = 'helpFunction';
    
    if(isset($_SERVER['argv'][1])) {
        $functionName = match($_SERVER['argv'][1]) {
            'read-all' => 'readAllFunction',
            'add' => 'addFunction',
            'find-birthdays' => 'findBirthdays',
            'delete' => 'deleteRecord',
            'clear' => 'clearFunction',
            'read-profiles' => 'readProfilesDirectory',
            'read-profile' => 'readProfile',
            'help' => 'helpFunction',
            default => 'helpFunction'
        };
    }

    return $functionName;
}
```
Добавили в функцию handleHelp() в template.function.php
```
function handleHelp() : string {
    $help = "Программа работы с файловым хранилищем \r\n";

    $help .= "Порядок вызова\r\n\r\n";
    
    $help .= "php /code/app.php [COMMAND] \r\n\r\n";
    
    $help .= "Доступные команды: \r\n";
    $help .= "read-all - чтение всего файла \r\n";
    $help .= "add - добавление записи \r\n";
    $help .= "find-birthdays - показать, у кого сегодня или ближайший день рождения \r\n";
    $help .= "delete - удалить запись по имени \r\n";
    $help .= "clear - очистка файла \r\n";
    $help .= "read-profiles - вывести список профилей пользователей \r\n";
    $help .= "read-profile - вывести профиль выбранного пользователя \r\n";
    $help .= "help - помощь \r\n";

    return $help;
}
```