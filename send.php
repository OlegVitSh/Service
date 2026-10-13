<?php
// Задаем заголовок JSON-ответа
header('Content-Type: application/json; charset=utf-8');

// Разрешаем только POST-запросы
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Метод не разрешен']);
    exit;
}

// 1. Укажите ваш рабочий Email для приема заявок
$to = 'ovzverev@yandex.ru'; // <-- ЗАМЕНИТЕ НА ВАШ EMAIL

// 2. Тема письма
$subject = 'Новая заявка с сайта LASER SERVICE';

// 3. Человекочитаемые названия полей
$field_names = [
    'model'          => 'Модель установки',
    'error_code'     => 'Код ошибки / Дефект',
    'city'           => 'Город / Предприятие',
    'company_city'   => 'Город / Предприятие',
    'phone'          => 'Телефон',
    'contact'        => 'Контакт (тел/email)',
    'name'           => 'Имя и должность',
    'purpose'        => 'Цель обращения',
    'service_format' => 'Формат обслуживания',
    'product_list'   => 'Заказ ЗИП',
    'inn'            => 'ИНН организации',
    'details'        => 'Дополнительные детали'
];

// 4. Формирование тела письма
$body = "ПОСТУПИЛА НОВАЯ ЗАЯВКА С САЙТА:\n";
$body .= "----------------------------------------\n\n";

$has_data = false;
foreach ($_POST as $key => $value) {
    $clean_value = trim($value);
    if (!empty($clean_value)) {
        $has_data = true;
        $label = isset($field_names[$key]) ? $field_names[$key] : htmlspecialchars($key);
        $body .= "• " . $label . ": " . htmlspecialchars($clean_value) . "\n";
    }
}

if (!$has_data) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Форма пуста']);
    exit;
}

$body .= "\n----------------------------------------\n";
$body .= "Дата и время: " . date('d.m.Y H:i:s') . "\n";
$body .= "IP отправителя: " . $_SERVER['REMOTE_ADDR'] . "\n";

// 5. Формирование корректных почтовых заголовков (UTF-8)
$domain = $_SERVER['SERVER_NAME'];
$from_email = 'no-reply@' . $domain;

$headers = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-type: text/plain; charset=utf-8';
$headers[] = 'From: LASER SERVICE <' . $from_email . '>';
$headers[] = 'Reply-To: ' . $from_email;
$headers[] = 'X-Mailer: PHP/' . phpversion();

// Кодируем тему письма в Base64 для исключения проблем с русскими буквами
$encoded_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

// 6. Отправка письма
if (mail($to, $encoded_subject, $body, implode("\r\n", $headers))) {
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Не удалось отправить почту']);
}
