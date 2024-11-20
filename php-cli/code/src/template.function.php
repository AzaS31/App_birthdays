<?php

function handleError(string $errorText) : string {
    return "\033[31m" . $errorText . " \r\n \033[97m";
}

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