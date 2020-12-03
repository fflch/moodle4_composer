Subindo o ambiente de desenvolvimento:

    composer install
    php -S 0.0.0.0:8888 -t moodle

Or:

    php -S 0.0.0.0:8888 -t moodle -c php.ini

Scrips:

- Versões dos plugins?
- acessar linkado para theme/edis/acessar/index.php
- moodle/monitor-http-ecv-conf-dist.php
- moodle/monitor-http-ecv.php
- moodle/lib/h5p tá errado???????
- moodle/lib/mdn-polyfills - problema com versões do php?
- moodle/local/local_mobile -> THIS PLUGIN IS NOT NECESSARY FOR MOODLE 3.5 ONWARDS
- moodle/mod/recordingsbn -> Warning!  This plugin has been phased out
- moodle/theme/bootstrapbase ? está no core: https://moodle.org/plugins/theme_bootstrap/ ?????

Plugins locais:

- moodle/auth/noostoa:verificar o que faz
- moodle/theme/edis: template atual do ediscipla
- moodle/blocks/usp_cursos
- moodle/enrol/stoa

Exemplo de configuração https atrás de um proxy:

    $CFG->wwwroot   = 'https://moodle.fflch.usp.br';
    $CFG->reverseproxy = true;


