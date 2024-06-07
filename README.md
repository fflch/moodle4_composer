Inspirado em:

    https://github.com/michaelmeneses/moodle-composer

Subindo um ambiente para desenvolvimento:

    composer install
    php -S 0.0.0.0:8888 -t moodle

Subindo o ambiente de desenvolvimento com php.ini customizado:

    php -S 0.0.0.0:8888 -t moodle -c php.ini

Configurações para depuração:

    @error_reporting(E_ALL | E_STRICT);  
    @ini_set('display_errors', '1');         
    $CFG->debug = (E_ALL | E_STRICT);   
    $CFG->debugdisplay = 1;

Gerando dummy data para densenvolvimento:

    php admin/tool/generator/cli/maketestsite.php --size=S

Exemplo de configuração https atrás de um proxy:

    $CFG->wwwroot   = 'https://moodle.fflch.usp.br';
    $CFG->reverseproxy = true;

Plugins que implementam Indicators:

    find . -iname "*indicator*"  | grep mod
    ls ./mod/page/classes/analytics/indicator                                                   
    activity_base.php  cognitive_depth.php  social_breadth.php


