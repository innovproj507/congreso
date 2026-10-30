<?php

namespace Modules\Landing\Config;

use CodeIgniter\Config\BaseConfig;

// Contenido editable de la landing (equivale a src/content/congreso.ts del demo en Next).
// Tarifas, fechas de preventa y formas de pago NO viven aquí: las entrega el módulo Eventos.
class Congreso extends BaseConfig
{
    public string $slug = 'congreso-2027';
    public string $nombre = 'LV Congreso Nacional de Pediatría';
    public string $edicion = 'Edición Esmeralda';
    public string $lema = 'Protegiendo el futuro con ciencia sin fronteras';
    public string $presidenta = 'Dra. Ivonne Abadía de Regalado';
    public string $fechaTexto = '17 al 19 de junio de 2027';
    public string $fechaInicioISO = '2027-06-17T09:00:00-05:00';
    public string $sede = 'Megapolis Convention Center';
    public string $correo = 'congresodepediatria@mestudiobtl.net';
    /** Enlace de reservas de Grand Tours. Se puede cambiar en .env: congreso.grandToursUrl = 'https://...'. Vacío = el botón se muestra como «próximamente». */
    public string $grandToursUrl = 'https://dmc.grandtours.com.pa/congreso-pediatria';

    /** Datos para pagos por ACH y Yappy. Se pueden cambiar en .env: congreso.bancoCuenta = '...' */
    public string $bancoBeneficiario = 'Sociedad Panameña de Pediatría';
    public string $bancoNombre = 'Banco Nacional de Panamá';
    public string $bancoCuenta = '10000354366';

    /** @var list<array{id:string,label:string}> */
    public array $menu = [
        ['id' => 'revive', 'label' => 'Revive 2026'],
        ['id' => 'bienvenida', 'label' => 'Bienvenida'],
        ['id' => 'inicio', 'label' => 'Aliados'], // el banner principal muestra a los aliados estratégicos
        ['id' => 'inscripciones', 'label' => 'Inscripciones'],
        ['id' => 'internacionales', 'label' => 'Internacionales'],
        ['id' => 'contacto', 'label' => 'Contacto'],
    ];

    /** @var list<string> */
    public array $incluye = [
        'Conferencias magistrales durante 3 días, de 9:00 a. m. a 4:00 p. m. aproximadamente',
        'Acceso directo a los conferencistas nacionales e internacionales',
        'Oportunidad de interactuar con las marcas participantes en la Expo Médica',
    ];

    /** @var list<string> */
    public array $pasosInscripcion = [
        'Elige tu categoría y la tarifa vigente.',
        'Realiza el pago por el medio de tu preferencia.',
        'Envía tu comprobante y datos a nuestro correo para confirmar tu inscripción.',
    ];

    /** @var list<string> */
    public array $requisitosTrabajos = [
        'Trabajo original relacionado con pediatría y salud infantil.',
        'Resumen estructurado (introducción, métodos, resultados, conclusiones).',
        'Al menos un autor inscrito en el congreso.',
        'Fecha límite y formato de envío: próximamente.',
    ]; // PLACEHOLDER: requisitos oficiales de la Dra. Aida

    /** @var list<array{dia:string,nota:string}> */
    public array $agenda = [
        ['dia' => 'Jueves 17 de junio', 'nota' => 'Conferencias magistrales'],
        ['dia' => 'Viernes 18 de junio', 'nota' => 'Conferencias magistrales'],
        ['dia' => 'Sábado 19 de junio', 'nota' => 'Conferencias magistrales y clausura'],
    ];

    /** @var list<array{q:string,a:string}> */
    public array $faqs = [
        ['q' => '¿Cuándo y dónde se realiza el congreso?', 'a' => 'Del 17 al 19 de junio de 2027, en el Megapolis Convention Center.'],
        ['q' => '¿Qué incluye la inscripción?', 'a' => 'Conferencias magistrales durante 3 días, de 9:00 a. m. a 4:00 p. m. aproximadamente, acceso directo a los conferencistas nacionales e internacionales y la oportunidad de interactuar con las marcas participantes en la Expo Médica.'],
        ['q' => '¿Cómo puedo pagar?', 'a' => 'Yappy, Visa, Mastercard, Clave y ACH Banco Nacional.'],
        ['q' => '¿Hasta cuándo aplica la preventa?', 'a' => 'Las tarifas de preventa rigen del 30 de octubre de 2026 al 15 de marzo de 2027. Luego aplican las tarifas regulares.'],
        ['q' => 'Soy participante internacional, ¿cómo reservo?', 'a' => 'A través de Grand Tours, que gestiona hoteles, tiquetes aéreos y traslados, con paquetes que incluyen la inscripción. Aplica para visitantes del extranjero.'],
        ['q' => '¿Cómo participo en el Premio Protegiendo el Futuro?', 'a' => 'Los requisitos de los trabajos libres se publicarán próximamente en esta página.'],
    ];

    public int $galeriaFotos = 8; // PLACEHOLDER: fotos del Congreso 2026
}
