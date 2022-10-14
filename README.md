Subindo o ambiente de desenvolvimento:

    composer install
    php -S 0.0.0.0:8888 -t moodle

Subindo o ambiente de desenvolvimento com php.ini customizado:

    php -S 0.0.0.0:8888 -t moodle -c php.ini

Inspirado em:

    https://github.com/michaelmeneses/moodle-composer

Exemplo de configuração https atrás de um proxy:

    $CFG->wwwroot   = 'https://moodle.fflch.usp.br';
    $CFG->reverseproxy = true;


