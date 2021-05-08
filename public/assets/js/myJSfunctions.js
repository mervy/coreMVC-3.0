/*Back to top*/
$(document).ready(function () {
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('.top').fadeIn();
        } else {
            $('.top').fadeOut();
        }
    });
    // scroll body to 0px on click
    $('.top').click(function () {
        $('.top').tooltip('hide');
        $('body,html').animate({
            scrollTop: 0
        }, 800);
        return false;
    });

    $('.top').tooltip('show');

});
/*******/

$('.phone').maskbrphone({
    useDdd: true,
    useDddParenthesis: true,
    dddSeparator: ' ',
    numberSeparator: '-'
});

/*Mensagem Uso de Cookies
 * Coloque entre head : 
 * <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.0.3/cookieconsent.min.css" />
 * e no rodapé (antes de </body>
 * <script src="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.0.3/cookieconsent.min.js"></script>
 * e o código abaixo
 * */
window.addEventListener("load", function () {
    window.cookieconsent.initialise({
        "palette": {
            "popup": {
                "background": "#fff", //"#216942",
                "text": "#000" //"#b2d192"
            },
            "button": {
                "background": "#afed71"
            }
        },
        "content": {
            "message": "Este site usa cookies para garantir que você obtenha a melhor experiência em nosso site.",
            "link": "Leia mais",
            "dismiss": "Compreendo!",
            "href": "/about"
        }
    })
});
/*fim cookies*/