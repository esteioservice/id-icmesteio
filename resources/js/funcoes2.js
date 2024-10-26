var idusuario="";

function isNumberKey(Key)
{
    var charCode = (Key.which) ? Key.which : event.keyCode
    if (charCode > 47 && charCode < 58 || charCode == 8)
        return true;
    return false;
}

function float2moeda(num) {

    x = 0;

    if(num<0) {
        num = Math.abs(num);
        x = 1;
    }
    if(isNaN(num)) num = "0";
    cents = Math.floor((num*100+0.5)%100);

    num = Math.floor((num*100+0.5)/100).toString();

    if(cents < 10) cents = "0" + cents;
    for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
        num = num.substring(0,num.length-(4*i+3))+'.'
            +num.substring(num.length-(4*i+3));
    ret = num + ',' + cents;
    if (x == 1) ret = ' - ' + ret;return ret;

}



function rodapefechar(email, senha){
    jQuery("#rodape-barra").hide();
    jQuery.cookie('rodapefechar', '1');
}

function loginajax(email, senha){

    if(email == ""){
        jQuery("#loadingcontato").hide();
        alert("Informe o seu email cadastrado em nosso site")
        document.getElementById("emailshare").focus();

        return;
    }
    if(senha== ""){
        jQuery("#loadingcontato").hide();
        alert("Informe a sua senha cadastrada em nosso site.")
        document.getElementById("passwordshare").focus();
        return;
    }

    jQuery("#loadingcontato").show();

    jQuery.ajax({
        type: "POST",
        cache: false,
        async: true,
        url: URLWEB+"/autenticacao/login.php",
        data: "acao=loginimportacontato&email="+email+"&password="+senha,
        success: function(msg){
            if(jQuery.trim(msg)=="0"){
                jQuery("#loadingcontato").hide();
                alert("usuário ou senha inválidos, por favor, verifique os seus dados e tente novamente.");
            }
            if(jQuery.trim(msg)=="01"){
                jQuery("#loadingcontato").hide();
                alert("Nós ainda não recebemos a sua validação de email, por favor, entre no seu email de cadastro e clique no link de confirmação.");
            }

            if(jQuery.trim(msg)==""){
                alert("Login realizado com sucesso. Agora infome o seu email e senha de alguma rede social como orkut, facebook, twitter, Badoo, Linkedin ou seu email e senha do gmail ou yahoo. ")
                jQuery.ajax({
                    type: "POST",
                    cache: false,
                    async: true,
                    url: URLWEB+"/util/OpenInviter/convidar.php",
                    data: "",
                    success: function(msg){
                        jQuery("#loadingcontato").hide();
                        jQuery("#naologado").html(msg);

                    }
                });
            }

        }
    });
}


function validaCPF(cpf)
{
    if (cpf == "00000000000" || cpf == "11111111111" || cpf == "22222222222" || cpf == "33333333333" || cpf == "44444444444" || cpf == "55555555555" || cpf == "66666666666" || cpf == "77777777777" || cpf == "88888888888" || cpf == "99999999999" || cpf.length < 11)
        return false;

    var a = [];
    var b = new Number;
    var c = 11;
    for (i=0; i<11; i++)
    {
        a[i] = cpf.charAt(i);
        if (i < 9) b += (a[i] * --c);
    }
    if ((x = b % 11) < 2)
    {
        a[9] = 0
    }
    else
    {
        a[9] = 11-x
    }
    b = 0;

    c = 11;
    for (y=0; y<10; y++)
        b += (a[y] *  c--);
    if ((x = b % 11) < 2)
    {
        a[10] = 0;
    }
    else
    {
        a[10] = 11-x;
    }
    if ((cpf.charAt(9) != a[9]) || (cpf.charAt(10) != a[10]))
    {
        return false;
    }

    return true;
}

function validaCNPJ(cnpj) {

    cnpj = cnpj.replace(/[^0-9]/g,'');
    if(cnpj == '') return false;

    if (cnpj.length != 14)
        return false;

    // Elimina CNPJs invalidos conhecidos
    if (cnpj == "00000000000000" ||
        cnpj == "11111111111111" ||
        cnpj == "22222222222222" ||
        cnpj == "33333333333333" ||
        cnpj == "44444444444444" ||
        cnpj == "55555555555555" ||
        cnpj == "66666666666666" ||
        cnpj == "77777777777777" ||
        cnpj == "88888888888888" ||
        cnpj == "99999999999999")
        return false;

    // Valida DVs
    tamanho = cnpj.length - 2
    numeros = cnpj.substring(0,tamanho);
    digitos = cnpj.substring(tamanho);
    soma = 0;
    pos = tamanho - 7;
    for (i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2)
            pos = 9;
    }
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado != digitos.charAt(0))
        return false;

    tamanho = tamanho + 1;
    numeros = cnpj.substring(0,tamanho);
    soma = 0;
    pos = tamanho - 7;
    for (i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2)
            pos = 9;
    }
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado != digitos.charAt(1))
        return false;

    return true;

}

function CheckEnter(Key)
{
    var charCode = (Key.which) ? Key.which : event.keyCode
    if (charCode > 9 && charCode < 14)
        return false;
    return true;
}

function SomenteNumero(e){
    var tecla=(window.event)?event.keyCode:e.which;
    if((tecla>47 && tecla<58)) return true;
    else{
        if (tecla==8 || tecla==0) return true;
        else  return false;
    }
}

function formatar_moeda(campo, separador_milhar, separador_decimal, tecla) {
    var sep = 0;
    var key = '';
    var i = j = 0;
    var len = len2 = 0;
    var strCheck = '0123456789';
    var aux = aux2 = '';
    var whichCode = (window.Event) ? tecla.which : tecla.keyCode;

    if (whichCode == 13) return true; // Tecla Enter
    if (whichCode == 8) return true; // Tecla Delete
    key = String.fromCharCode(whichCode); // Pegando o valor digitado
    if (strCheck.indexOf(key) == -1) return false; // Valor inválido (não inteiro)
    len = campo.value.length;
    for(i = 0; i < len; i++)
        if ((campo.value.charAt(i) != '0') && (campo.value.charAt(i) != separador_decimal)) break;
    aux = '';
    for(; i < len; i++)
        if (strCheck.indexOf(campo.value.charAt(i))!=-1) aux += campo.value.charAt(i);
    aux += key;
    len = aux.length;
    if (len == 0) campo.value = '';
    if (len == 1) campo.value = '0'+ separador_decimal + '0' + aux;
    if (len == 2) campo.value = '0'+ separador_decimal + aux;

    if (len > 2) {
        aux2 = '';

        for (j = 0, i = len - 3; i >= 0; i--) {
            if (j == 3) {
                aux2 += separador_milhar;
                j = 0;
            }
            aux2 += aux.charAt(i);
            j++;
        }

        campo.value = '';
        len2 = aux2.length;
        for (i = len2 - 1; i >= 0; i--)
            campo.value += aux2.charAt(i);
        campo.value += separador_decimal + aux.substr(len - 2, len);
    }

    return false;
}

var nome;
var email="";
function envianewsletter(){

    email = J("#newsletterClientEmail").val();
    nome = J("#newsletterClientNome").val();

    if(nome == "" || nome == "Seu nome"){

        alert("Informe o seu nome")
        document.getElementById("newsletterClientNome").focus();
        return;
    }
    if(email == "" || email == "Seu e-mail"){

        alert("Informe o seu email")
        document.getElementById("newsletterClientEmail").focus();
        return;
    }

    J.ajax({
        type: "POST",
        cache: false,
        async: false,
        url: URLWEB+"/newsletter.php",
        data: "email="+email+"&nome="+nome,
        success: function(msg){

            if( jQuery.trim(msg)=="1"){
                jQuery("#loadingcontatoheader").html("");
                alert("Obrigado ! Seu email foi cadastrado com sucesso!");
            }
            else {
                jQuery("#loadingcontatoheader").html("");
                alert(msg)
            }
        }
    });
}


function envianewsletterhome(email,cidade){


    if(email == "" || email == "Insira seu e-mail"){
        alert("Você esqueceu de informar o seu email !")
        document.getElementById("emailnewshome").focus();
        return;
    }

    if(cidade == ""){
        alert("Nos informe a cidade no qual deseja receber as ofertas.")
        document.getElementById("websites3").focus();
        return;
    }

    jQuery("#loadingcontato").html("<img style=margin-left:50px; src="+URLWEB+"/skin/padrao/images/ajax-loader.gif> Cadastrando em nossa newsletter...");


    J.ajax({
        type: "POST",
        cache: false,
        async: false,
        url: URLWEB+"/newsletter.php",
        data: "email="+email+"&city_id="+cidade,
        success: function(msg){

            if(msg=="1"){
                jQuery("#loadingcontato").html("");
                alert( "Cadastro realizado com sucesso. Vamos redirecionar para a cidade escolhida. Aproveite e compre já o seu ingresso reembolsável !" );
                Dialog.okCallback();
                location.href=URLWEB+"/index.php?idcidade="+cidade;

            }
            else {
                alert( msg );
                jQuery("#loadingcontato").html("");
                Dialog.okCallback();

            }

        }
    });
}


function voltaimportarcontatos(){

    jQuery.ajax({
        type: "POST",
        cache: false,
        async: false,
        url: URLWEB+"/util/OpenInviter/convidar.php",
        data: "",
        success: function(msg){
            jQuery("#conteudo").html(msg);
        }
    });
}

function showLoader(){
    J('.search-background').fadeIn(200);
}
function hideLoader(){
    J('.search-background').fadeOut(200);
};

function clicamenu( idcategoria){

    showLoader();

    J("#paging_button li").css({'background-color' : ''});
    J(this).css({'background-color' : '#D8543A'});

    J("#pgofertas").load(URLWEB+"/include/paginacao_post.php?idcategoria="+idcategoria+"&page=1", hideLoader);
    //J("#numeropaginas").html("");
    J("#paging_button").load(URLWEB+"/include/paginacao.php?idcategoria="+idcategoria+"&page=1", hideLoader);

    return;

}

J(window).unload(function() {

    // alert(1)
});


function atualiza_click(idoferta){

    jQuery.ajax({
        type: "POST",
        cache: false,
        async: true,
        url: URLWEB+"/include/atualiza_click.php?&idoferta="+idoferta,
        data: "",
        success: function(msg){
            //jQuery(window.document.location).attr('href',site);
        }
    });

}


function cadastra_pedido(){

    quantidade 		= J("#quantidade_pedido").val();
    valorpedido 	= J("#valorpedido").val();
    valor_unitario 	= J("#valor_unitario").val();
    gateway 		= J("#gateway").val();
    idoferta 		= J("#idoferta").val();

    if(idusuario==""){
        idusuario 		= J("#idusuario").val();
    }
    if(idusuario==""){
        idusuario  = LOGINUID
    }

    jQuery.ajax({
        type: "POST",
        cache: false,
        async: true,
        url: URLWEB+"/include/get_num_pedido.php?city_id="+CITY_ID+"&utm="+J("#utm").val()+"&idoferta="+idoferta+"&idusuario="+idusuario+"&quantidade="+quantidade+"&valorpedido="+valorpedido+"&valor_unitario="+valor_unitario+"&gateway="+gateway,
        data: "",
        success: function(idpedido){
            //alert(idpedido) ;
            if(idpedido==""){
                alert("Não foi possível criar este pedido, por favor, entre em contato conosco!")
                return;
            }
            J("#idpedido").val(idpedido);

            preenche_formularios(quantidade,valorpedido,valor_unitario,idoferta,idpedido)

            if(J("#utm").val()==0){
                J("#"+gateway).submit();
            }
        }
    });

}
function preenche_formularios(quantidade,valorpedido,valor_unitario,idoferta,idpedido){

//***************** formulario pagseguro

    J("#item_id_1").val(idpedido);
    J("#item_descr_1").val(J("#titulo").val());
    J("#item_quant_1").val(quantidade);
    J("#item_valor_1").val(valor_unitario);
    J("#ref_transacao").val(idpedido);
    J("#reference").val(idpedido);

//***************** formulario pagamento digital

    J("#id_pedido").val(idpedido);
    J("#produto_codigo_1").val(idpedido);
    J("#produto_descricao_1").val(J("#titulo").val());
    J("#produto_qtde_1").val(quantidade);
    J("#produto_valor_1").val(valor_unitario);


//***************** formulario paypall
    valor_paypal = valorpedido.replace(",",".");

    J("#item_number").val(idpedido);
    J("#item_name").val(J("#titulo").val());
    J("#amount").val(valor_paypal);

//***************** formulario moip

    valor_moip = valorpedido.replace(",","");
    valor_moip = valor_moip.replace(".","");


    J("#id_transacao").val(idpedido);
    J("#descricao").val(J("#titulo").val());
    J("#nome").val(J("#titulo").val());
    J("#valor").val(valor_moip);

//***************** formulario dinheiro mail

    J("#transaction_id").val(idpedido);
    J("#item_name_1").val(J("#titulo").val());
    J("#item_quantity_1").val(quantidade);
    J("#item_ammount_1").val(valor_unitario);

//***************** formulario mercado pago

    J("#item_id").val(idpedido);
    J("#name").val(J("#titulo").val());
    J("#price").val(valorpedido);

}

function set_utm(){
    J("#utm").val(1);
    //cadastra_pedido()
}

function enviacart(idoferta){

    jQuery.colorbox({html:"<img src="+URLWEB+"/skin/padrao/images/ajax-loader2.gif> Registrando sua opção. Aguarde..."});

    J("#idoferta").val(idoferta);
    J("#dadospedido").attr("action",URLWEB+"/carrinho/"+idoferta);
    J('#dadospedido').submit();
}

function abreboxOfertasPacote(conteudo){
    J.colorbox({html:conteudo});
}

function ordenarBusca(ord){

    J("#ordena").val(ord);

    J('<input>').attr('type', 'hidden').attr('name', 'ordena').attr('value', ord).appendTo('#formpesquisa3');

    J('#formpesquisa3').submit();
}
function buscaanunciosrevenda(idparceiro){

    J('<input>').attr('type', 'hidden').attr('name', 'idparceiro').attr('value', idparceiro).appendTo('#formparceiro');

    J('#formparceiro').submit();
}

function pesquisa(){

    if(J("#cppesquisa").val()=="" || J("#cppesquisa").val()=="O que está procurando ?"){
        alert("Por favor, informe algo para que possamos procurar pra você.");
        return;
    }

    jQuery.colorbox({html:"<img src="+URLWEB+"/skin/padrao/images/ajax-loader2.gif> Estamos fazendo a pesquisa. Aguarde..."});

    J('#formpesquisa').submit();
}

function removecarconfirm(id){

    if(confirm('Você tem certeza que deseja remover este item do seu carrinho de compras?')){
        removeCarrinho(id,'bloco')
    }
}
function achoufrete(){
    temfrete = false;
    J.each(J('.infoItem'), function() {
        if (J(this).attr('data-frete') == "1") {
            temfrete = true;
            J('#existefrete').val("sim");

        }
    });
    if(temfrete){
        return true;
    }
    else{
        return false;
    }
}


var valorvalecompras;
valorvalecompras = rp(float2moeda(parseFloat(0)));

function consultavalecompras(codigo){

    if(codigo==""){
        jQuery.colorbox({html:"Por favor, informe o código de um vale compras"});
        return;
    }
    jQuery.ajax({
        type: "POST",
        cache: false,
        async: true,
        url: URLWEB+"/include/funcoes.php?acao=consultavalecompras&codigo="+codigo,
        data: "",
        success: function(retorno){
            retornoarr = retorno.split('##');
            status = jQuery.trim(retornoarr[0]);
            mensagemerro = jQuery.trim(retornoarr[1]);
            valorvalecompras =  jQuery.trim(retornoarr[1]) ;

            if(status == "erro"){
                J.colorbox({html:"<div width:'400px'>"+mensagemerro+"</div>"});
            }
            else{
                jQuery('.valorvalecompra').html('<p style=color:#1a75ce>Cumpom de desconto: R$ '+valorvalecompras+'</p>');
                novoSubtotal();
                jQuery.colorbox({html:"Parabéns! Você conseguiu um desconto de R$ "+valorvalecompras+" neste carrinho de compras. Iremos concretizar o desconto ao finalizar o pedido."});

                jQuery("#codigocupom").val(codigo);
            }
        }
    });

}


function calculaTotalbyclick(metodo_entrega_escolhido)  {
    erro_pac=false;

    jQuery('.prazoentrega').show();
    valor_metodo_escolhido = J('#'+metodo_entrega_escolhido).attr('data-valorfrete');
    prazo_metodo_escolhido = J('#'+metodo_entrega_escolhido).attr('data-prazo');
    total_parcial_produtos = 0;
    total_produtos = 0;
    novototalbyclick = 0;

    jQuery.each(jQuery('.totalItem'), function(atual) {
        total_parcial_produtos = jQuery(this).attr('data-total-price');
        total_produtos  = parseFloat(total_produtos) + parseFloat(total_parcial_produtos);
    });

    if(typeof(valor_metodo_escolhido)  === "undefined"){
        valor_metodo_escolhido=0;
    }
    if(valor_metodo_escolhido==""){
        valor_metodo_escolhido=0;
    }
    if(typeof(prazo_metodo_escolhido)  === "undefined"){
        prazo_metodo_escolhido="";
    }
    if(prazo_metodo_escolhido==""){
        prazo_metodo_escolhido=0;
    }
    novototalbyclick = rp(float2moeda(parseFloat(valor_metodo_escolhido) + parseFloat(total_produtos)));

    jQuery('.valorFrete').html('Frete: R$ ' +  float2moeda(valor_metodo_escolhido));
    if(prazo_metodo_escolhido !=""){
        jQuery('.prazoentrega').html('Prazo de entrega: ' + prazo_metodo_escolhido + " dia(s)")
    }
    else{
        jQuery('.prazoentrega').hide();
    }
    if(parseFloat(valorvalecompras) >= parseFloat(novototalbyclick)){
        alert("O valor de seu vale compras não pode ser maior do que o valor total do carrinho. Você tem um vale compras de R$ "+parseFloat(valorvalecompras))
        location.href = URLWEB+"/carrinho";
        return;
    }
    novototalbyclick =  rp(float2moeda(parseFloat(novototalbyclick  -  valorvalecompras))) ;
    jQuery('.totalpedidobk').html('Total: R$ ' + float2moeda(novototalbyclick));


}

function calculaTotal(subtotal)  {

    totalpedido = parseFloat(valorfrete) + parseFloat(subtotal);

    if(totalpedido < 0) {
        totalpedido = 0;
    }
    // descomentar para casas de milhares exemplo: valores acima de 1000
    //totalpedido = rp(totalpedido)

    valorvalecompras = rp(valorvalecompras)

    if(parseFloat(valorvalecompras) >= parseFloat(totalpedido)){
        alert("O valor de seu vale compras não pode ser maior do que o valor total do carrinho. Você tem um vale compras de R$ "+parseFloat(valorvalecompras))
        location.href = URLWEB+"/carrinho";
        return;
    }
    totalpedido = parseFloat(totalpedido) -  parseFloat(valorvalecompras);

    jQuery('.totalpedidobk').html('Total: R$ ' + (float2moeda(totalpedido.toFixed(2))));
    totalpedido = "";

}
function rp(valor){
    valor = valor.toString();
    valor = valor.replace(".","");
    valor = valor.replace(",",".");
    return valor;
}

function omitevalorfrete(){
    jQuery('.valorFrete').html('');
    jQuery('#valorfrete').val("");
}

function seta_erro(modo){

    if(modo==04510 ){	// se deu erro no Pac, não deixa prosseguir
        b_erro = true;
        jQuery('.valorFrete').html('');
        jQuery('#berro').val(1);
        jQuery("#"+modo).hide();
    }

}
var valorfrete;
var erro_pac;
var buscou_valores_frete = false;

function calculafrete(cepdestino, modo) {
    if(achoufrete()){
        buscou_valores_frete = true;
        //alert("buscou_valores_frete")

        //jQuery("#btncart").attr("disabled","true");
        id_modoenvio = J('input[name="modo_envio"]:checked').val();
        jQuery.ajax({
            type: "POST",
            cache: false,
            async: false,
            url: URLWEB+"/include/funcoes.php",
            data: "acao=calculafrete&cep_destino="+cepdestino + "&modo=" + modo,
            success: function(retorno){
                retornoarr = retorno.split('-');
                Status = jQuery.trim(retornoarr[0]);
                valor = jQuery.trim(retornoarr[1]);
                prazo = jQuery.trim(retornoarr[2]);
                envio = jQuery.trim(retornoarr[3]);

                if(Status=="sucesso"){
                    if(jQuery.trim(valor)!="0,00"){
                        valorfrete = rp(valor);
                        jQuery('.valorFrete_'+modo).html(' - R$ ' + valor);
                        if(prazo!=""){
                            if(diasadicionaisfrete==""){
                                diasadicionaisfrete = 0
                            }
                            prazo =  parseInt(prazo) + parseInt(diasadicionaisfrete);
                            jQuery('.prazoentrega_'+modo).html('- Prazo: ' + prazo + " dia(s)");
                        }
                        J('#'+modo).show();
                        J('#'+modo).attr('data-valorfrete',valorfrete) ;
                        J('#'+modo).attr('data-prazo',prazo);
                        subtotal = rp(total);
                        // SETA AS INFORMAÇÕES NO SUBTOTAL
                        if( id_modoenvio == modo ){
                            calculaTotal(subtotal);
                            jQuery('.valorFrete').html('Frete: R$ ' + valor);
                            jQuery('.prazoentrega').html('Prazo de entrega: ' + prazo + " dia(s)");
                            jQuery('#valorfrete').val(valorfrete);
                            J( "#"+id_modoenvio).prop( "checked", true );
                            J("#"+id_modoenvio).click();

                        }
                    }
                    else{
                        seta_erro(modo)
                    }
                }
                else{
                    seta_erro(modo);

                    if(valor==""){
                        valor = "Me desculpe, site dos correios temporariamente fora do ar. Aguarde algumas horas e tente novamente.";
                    }
                    if(modo==04510 ){
                        erro_pac = true;
                    }
                    /* simulacao de frete com ceps invalidos ou que o correio nao entrega para determinados serviços*/
                    if(erro_pac){   // se nao entrega para pac, nao entrega para sedex
                        var elements = document.getElementsByName('modo_envio');
                        for(var i = 0; i < elements.length; i++)
                        {
                            idmodo = elements[i].id;
                            ativo =  J("#"+idmodo).attr("style");
                            if(ativo == "display:block;") {
                                elements[i].checked = true;
                                elements[i].click() ;
                                break;
                            }
                        }
                    }
                    else if(modo == id_modoenvio){ // se deu erro mas nao foi no pac e o metodo está checked em sedex ou sedex 10, muda o click em pac
                        //alert("deu erro - e o id do modo corrente é igual ao clicado (sedex ou sedex10), agora alterando para pac")
                        alert(retorno)
                        J( "#04510").prop( "checked", true );
                        J("#04510").click();
                    }
                }
            }
        });
    }

}


function removeCarrinho(id,pag) {
    URL = URLWEB + '/index.php?page=remove_carrinho';
    jQuery.ajax({
        url: URL,
        type: "POST",
        data: {id: id},
        success: function(r) {
            if(pag=="bloco"){
                var preco = "";
                var qty = "";
                var total = 0;
                jQuery('#cart-sidebar_'+id).fadeOut(300, function() {
                    }
                );
                jQuery('#cart-sidebar_'+id).remove();
                jQuery.each(jQuery('.price'), function(chave, valor) {
                    preco = jQuery(this).attr('preco');
                    qty = jQuery(this).attr('qtde');

                    if (!isNaN(qty) && qty > 0) {
                        novoValor = qty * preco;
                        total += parseFloat(novoValor);
                    }
                });
                jQuery('#totalitenscarrinho').html('Total: R$ '+total.toFixed(2));
                if(total==0){
                    jQuery('#btfinalped').hide();
                }
            }
            else{
                jQuery('#tr_'+id).fadeOut(0, function() {
                        jQuery('#tr_'+id).remove();

                        if (jQuery('.linhaItem:visible').size() <= 0) {
                            alert('Você não possui mais nenhum item em seu carrinho, estamos redirecionando você para a página inicial.');
                            location.href = URLWEB;
                        }
                        else{
                            novoSubtotal();
                        }
                    }
                );
                if (jQuery('.linhaItem:visible').size() > 0) {

                    novoSubtotal();
                }
            }
        },
        error: function() {
            alert('Houve um erro ao remover o item');
        }
    });
}

function fechapedido(){
    var b_erro= false;

    if(achoufrete() && jQuery('#berro').val() == "1"){
        alert("0001 - Houve um erro ao calcular este frete.");
        return;
    }
    if(achoufrete() && jQuery('#valorfrete').val() == ""){
        alert("0002 - Houve um erro ao calcular este frete. Por favor, verifique junto aos correios se o seu cep está correto ou se houve mudança de cep, reinicie a sua compra e tente novamente.");
        return;
    }
    if(achoufrete()){
        if(!jQuery("input[type='radio'][name='modo_envio']").is(':checked'))
        {
            alert("Escolha um modo de envio");
            return;
        }
    }

    var id;
    jQuery.each(jQuery('.qty_item'), function(chave, valor) {

        id =  jQuery(this).attr('data-item');

        if(jQuery('#id_option').length == 1) {

            var id_option = jQuery('#id_option').val();
        }
        else {

            var id_option = "";
        }

        jQuery.ajax({
            type: "POST",
            cache: false,
            async: false,
            url: URLWEB+"/include/funcoes.php",
            data: "acao=verifica_regras_pre_compra&id=" + id + "&id_option=" + id_option,
            success: function(retorno){

                if(jQuery.trim(retorno)!=""){
                    b_erro = true;
                    //jQuery.colorbox({html:"<font color='black' size='10'>"+retorno+"</font>" });
                    jQuery('#erro_'+id).html("<span style='color:#FE5677;'>"+retorno+"</font>");
                }

            }
        });

    });

    if(b_erro == false){

        jQuery.colorbox({html:"<img src="+URLWEB+"/skin/padrao/images/ajax-loader2.gif> Estamos fechando o seu pedido. Por favor, aguarde"});
        J("#formularioCarrinho").submit();
    }
    else{
        jQuery.colorbox({html:"Por favor, corrija os campos em vermelho e tente novamente" });
    }
}

function novoSubtotal(cep) {

    if(typeof(cep)  === "undefined"){
        cep = "";
    }
    if(cep !=""){
        cepdestino = cep
    }
    total = 0;
    jQuery.each(jQuery('.totalItem'), function(atual) {
        totalparcial = jQuery(this).attr('data-total-price');
        total  = parseFloat(total) + parseFloat(totalparcial)
    });
    total = float2moeda(total);
    jQuery('.valorSubtotal').html('R$ '+total );
    id_modoenvio = J('input[name="modo_envio"]:checked').val();
    if(achoufrete()){
        //calculaTotal(total);
        calculaTotalbyclick(id_modoenvio);
    }
    else{
        omitevalorfrete();
    }
    jQuery.each(jQuery('.metodos_entrega'), function(atual) {
        metododosistema = jQuery(this).attr('data-sistema');
        modo = jQuery(this).attr('id');
        if(metododosistema=="s"){
            verfrete(cepdestino, modo);
        }
    });
}

function verfrete(cep,modo){
    calculafrete(cep,modo);
}
function consultacep(cep){
    cep = cep.replace('-', '');
    //alert(cep);
    novoSubtotal(cep)
}
function altera_metodo_entrega(metodo_entrega_escolhido){
    nomemetodo = J("#"+metodo_entrega_escolhido).attr('data-titulo');
    //J('#idmetodo').val(metodo_entrega_escolhido);
    //J('#cdc').val(metodo_entrega_escolhido);
    J('.nome_metodo').html("<b>"+nomemetodo+"</b> - ");
    calculaTotalbyclick(metodo_entrega_escolhido);
}
function alteraQty(id, qty, id_option) {
    url = URLWEB+'/index.php?page=altera_carrinho';
    jQuery.ajax({
        url: url,
        type: "POST",
        data: {id:id, qty:qty, id_option:id_option},
        success: function(retorno){
            if(retorno.trim() != "" && retorno.trim() != undefined) {
                jQuery('#btn-finalize-cart, #btncart').css('display', 'none');
                alert(retorno);
                return false;
            }
            else {
                jQuery('#btn-finalize-cart, #btncart').css('display', 'block');
                return false;
            }
        }
    });
}

function removerAcentos(newStringComAcento) {

    var string = newStringComAcento;
    var mapaAcentosHex = {
        a : /[\xE0-\xE6]/g,
        e : /[\xE8-\xEB]/g,
        i : /[\xEC-\xEF]/g,
        o : /[\xF2-\xF6]/g,
        u : /[\xF9-\xFC]/g,
        c : /\xE7/g,
        n : /\xF1/g
    };

    for ( var letra in mapaAcentosHex ) {
        var expressaoRegular = mapaAcentosHex[letra];
        string = string.replace( expressaoRegular, letra );
    }

    return string;
}


function mascaraMutuario(o,f){
    v_obj=o
    v_fun=f
    setTimeout('execmascara()',1)
}

function execmascara(){
    v_obj.value=v_fun(v_obj.value)
}

function cpfCnpj(v){

    //Remove tudo o que não é dígito
    v=v.replace(/\D/g,"")

    if (v.length < 14) { //CPF

        //Coloca um ponto entre o terceiro e o quarto dígitos
        v=v.replace(/(\d{3})(\d)/,"$1.$2")

        //Coloca um ponto entre o terceiro e o quarto dígitos
        //de novo (para o segundo bloco de números)
        v=v.replace(/(\d{3})(\d)/,"$1.$2")

        //Coloca um hífen entre o terceiro e o quarto dígitos
        v=v.replace(/(\d{3})(\d{1,2})$/,"$1-$2")


    } else { //CNPJ

        //Coloca ponto entre o segundo e o terceiro dígitos
        v=v.replace(/^(\d{2})(\d)/,"$1.$2")

        //Coloca ponto entre o quinto e o sexto dígitos
        v=v.replace(/^(\d{2})\.(\d{3})(\d)/,"$1.$2.$3")

        //Coloca uma barra entre o oitavo e o nono dígitos
        v=v.replace(/\.(\d{3})(\d)/,".$1/$2")

        //Coloca um hífen depois do bloco de quatro dígitos
        v=v.replace(/(\d{4})(\d)/,"$1-$2")


    }
    return v
}


function mascaraTelefone(o,f){
    v_obj=o
    v_fun=f
    setTimeout('execmascara()',1)
}

function execmascara(){
    v_obj.value=v_fun(v_obj.value)
}

function telDig(v){

    //Remove tudo o que não é dígito
    v=v.replace(/\D/g,"")

    if (v.length < 11) { //8 dígitos (fixo e cels antigos)

        //Coloca um parentese no começo
        v=v.replace(/(\d{0})(\d)/,"$1($2")

        //Coloca um parentese após o segundo dígito
        v=v.replace(/(\d{2})(\d)/,"$1)$2")

        //Coloca um hífen depois do bloco de quatro dígitos
        v=v.replace(/(\d{4})(\d)/,"$1-$2")


    } else{ //9 dígitos (novos cels)

        //Coloca um ponto entre o terceiro e o quarto dígitos
        v=v.replace(/(\d{0})(\d)/,"$1($2")

        //Coloca um ponto entre o terceiro e o quarto dígitos
        //de novo (para o segundo bloco de números)
        v=v.replace(/(\d{2})(\d)/,"$1)$2")

        //Coloca uma barra entre o oitavo e o nono dígitos
        //v=v.replace(/\.(\d{3})(\d)/,".$1/$2")

        //Coloca um hífen depois do bloco de quatro dígitos
        v=v.replace(/(\d{5})(\d)/,"$1-$2")


    }
    return v
}
