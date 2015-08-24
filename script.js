var delay = (function () {
    var timer = 0;
    return function (callback, ms) {
        clearTimeout(timer);
        timer = setTimeout(callback, ms);
    };
})();


$(document).ready(function () {
    focusInput();
    clearSyncType(); // delete any previous synctype commands

    runCommand("abort"); //
    $("#input").keyup(function (data, res) {
        if (data.which == 13) {
            // enter key pressed
            runCommand($('#input')[0].value);
            this.value = "";
            this.autocomplete.cancelSearch = true;
            // todo: if there is only 1 (account|Command) or suggestion, use that value. the shop and lugins can do this....
        } else {

            // change the value on the other screens as well
            // todo: this is currently delayed by one edit. Obviously annoying. does not seemt o be the case anymore.
            localStorage.setItem('synctype', this.value);

            // do some CSS filtering to match the input (somewhat)
            delay(function () {
                showOrHideAccounts($('#input')[0].value);
                // runCommand($('#input')[0].value);

            }, 50);

        }
    }).autocomplete({
        // handle clicks on the auto complete list
        // todo: have autocomplete on any change on the input, including synctype.
        delay: 0, source: availableCommands, select: function(event, ui) {
            if(ui.item){
                runCommand(ui.item.value,"");
                /* todo: close autocomplete, clear input */

                // do not type the value in the textbox
                // http://stackoverflow.com/questions/11607938/clear-text-box-in-jquery-autocomplete-after-selection
                this.value = "";
                return false;
            }
        }
    }).keyup(function (e) {
        if(e.which === 13) {
            $(".ui-menu-item").hide();
        }
    });

    // bind an abort routine to the escape key. it types abort in the input.
    // todo: does not work in firefox?
    $(document).keyup(function(e) {
        if (event.which == 27) {
            runCommand("abort");
        }
    });

    // this list is extended with output from various plugins. Abort is always availab.e
    var availableCommands = [
        "abort"
    ];


    // and set the abort button to some abort function.
    $('.eternalAbort').click(function () {
        runCommand("abort");
    });



    // only sycntype when the input element is not in focus.
    // the input element is reserved for editing of the command, and should behave like a normal input.

    // support type anywhere in the screen. This is synced to the textbox.
    // this means you can scan even when you are out of focus, etc
    // keydown does not understand lower case, only keypressed does this.
    // yet keypress does not understand control characters
    // http://jsfiddle.net/9TyzP/3/
    $(document).keypress(function(e) {
        // type a normal character

        if (!$("#input").is(':focus')) {
            localStorage.setItem('synctype', localStorage.getItem('synctype') + String.fromCharCode(e.which));
            $("#input")[0].value = localStorage.getItem('synctype');
        }
    });

    // control characters are handled with keydown (backspace and enter)
    $(document).keydown(function(e) {
        if (!$("#input").is(':focus')) {
            if (e.which == 13) {
                // ascii enter
                runCommand($('#input')[0].value);
            } else if (e.which == 8) {
                // backspace
                // this does not work using localstorage.getitem completely written out in a single line...
                var str = localStorage.getItem('synctype');
                localStorage.setItem('synctype', str.substring(0, str.length - 1));
                $("#input")[0].value = localStorage.getItem('synctype');

                // prevent going to the previous page after hitting backspace
                e.preventDefault();
            }
        }
    });
    //*/
});


// sycnhronize typing over multiple windows, using local storage
// note that only external tabs are changed, you have to update the local tabs yourself
window.addEventListener('storage', onSyncType, false);

// show synctype on screen (note: the other tabs)
function onSyncType(storageEvent, data) {
    // console.log(storageEvent, data);
    if (storageEvent.key == "synctype" && storageEvent.newValue != "") {
        $("#input")[0].value = localStorage.getItem('synctype');
    }
};

function clearSyncType(){
    localStorage.setItem('synctype',"");
}


// todo: add running plugin in localstorage, so second screens can display the correct stuff...



// todo: it should be best if all alternatives for a certain account or product are also used.
// todo: the casing is a problem, it now is case senstive.
function showOrHideAccounts(input){

    if (input.length > 0) {
        // show some accounts...
        $(".Account").css( "visibility","hidden");
        $(".Account").css( "display","none");
        $(".Account[id*=" + input + "]").css("visibility", "visible");
        $(".Account[id*=" + input + "]").css("display", "inline-block");

        $(".Command").css( "visibility","hidden");
        $(".Command").css( "display","none");
        $(".Command[id*=" + input + "]").css("visibility", "visible");
        $(".Command[id*=" + input + "]").css("display", "inline-block");
    } else {
        $(".Account").css( "visibility","visible");
        $(".Account").css( "display","inline-block");

        $(".Command").css( "visibility","visible");
        $(".Command").css( "display","inline-block");
    }
}

// why not mutate directly on the object?
function createFriendlyId(str){
    str = str.toLowerCase();
    str = str.replace( /[^a-z0-9]/g, '' );
    return str;
}

function focusInput() {
    $('#input').focus();
}



function createToc(){
    $("#ToC").empty();
    if ($('h3').length == 0) {return;} // do not make a toc when there is no text

    var ToC =
        "<nav role='navigation' class='table-of-contents'>" +
        "<h2>On this page:</h2>" +
        "<ul>";

    var newLine, el, title, link;

    $("h3").each(function() {

        el = $(this);
        title = el.text();
        link = "#" + el.attr("id");

        newLine =
            "<li>" +
            "<a href='" + link + "'>" +
            title +
            "</a>" +
            "</li>";

        ToC += newLine;

    });

    ToC +=
        "</ul>" +
        "</nav>";

    $("#ToC").prepend(ToC);
}


// this is the actual "showing stuff on screen"
function renderOutput(output){
    clearUi();
    if (output["ArbitraryHtml"]) {renderArbitraryHtml(output["ArbitraryHtml"]);}
    if (output["Hint"]) {       renderHint(output["Hint"]);}
    if (output["BackgroundHtml"]) {renderBackgroundHtml(output["BackgroundHtml"]);}
    if (output["Page"]) {       renderPages(output["Page"]);}
    if (output["AccountCommand"]) { renderAccountCommands(output["AccountCommand"]);}
    if (output["Command"]) {    renderCommands(output["Command"]);}
    if (output["InvolvedAccountCommand"]) {renderInvolvedAccounts(output["InvolvedAccountCommand"]);}
    if (output["Product"]) {    renderProducts(output["Product"]);}
    if (output["Suggestion"]){  renderSuggestions(output["Suggestion"]);}
    if (output["FinalCommand"]){renderFinalsCommands(output["FinalCommand"]);}
    if (output["Value"]){       renderValues(output["Value"]); }
    if (output["Dataset"]) {    renderDatasets(output["Dataset"]); }
    if (output["Image"]) {      renderImages(output["Image"]); }
    createToc();
}

function storeLocally(output){
    if (output["ArbitraryHtml"]) {localStorage.setItem('ArbitraryHtml', JSON.stringify(output["ArbitraryHtml"]));}
    if (output["Hint"])          {localStorage.setItem('Hint', JSON.stringify(output["Hint"]));}
    if (output["BackgroundHtml"]){localStorage.setItem('BackgroundHtml', JSON.stringify(output["BackgroundHtml"]));}
    if (output["Page"])          {localStorage.setItem('Page', JSON.stringify(output["Page"]));}
    if (output["AccountCommand"]){localStorage.setItem('AccountCommand', JSON.stringify(output["AccountCommand"]));}
    if (output["Command"])       {localStorage.setItem('Command', JSON.stringify(output["Command"]));}
    if (output["InvolvedAccountCommand"]) {localStorage.setItem('InvolvedAccountCommand', JSON.stringify(output["InvolvedAccountCommand"]));}
    if (output["Product"])       {localStorage.setItem('Product', JSON.stringify(output["Product"]));}
    if (output["Suggestion"])    {localStorage.setItem('Suggestion', JSON.stringify(output["Suggestion"]));}
    if (output["FinalCommand"])  {localStorage.setItem('FinalCommand', JSON.stringify(output["FinalCommand"]));}
    if (output["Value"])         {localStorage.setItem('Value', JSON.stringify(output["Value"]));}
    if (output["Dataset"])       {localStorage.setItem('Dataset', JSON.stringify(output["Dataset"]));}
    if (output["Image"])         {localStorage.setItem('Image', JSON.stringify(output["Image"]));}
}

function clearUi(){
    // backgroundHtml is not deleted every request.
    $('#Commands').empty();
    $('#Accounts').empty();
    $('#Products').empty();
    $('#Finals').empty();
    $('#Values').empty();
    $('#Datasets').empty();
    $('#Pages').empty();
    $('#InvolvedAccounts').empty();
    $('#Images').empty();
}

function renderDatasets(datasets){
    $.each(datasets, function (index, value) {
        var gridid = Math.floor((Math.random() * 10000) + 1);
        $('#Datasets').append("<h3 id='" + createFriendlyId(value.name) + "'>" + value.name + "</h3><div id='dataset"+gridid+"'></div>");
        $('#dataset'+gridid).columns({
            data: value.data
        })
    });
}

function renderValues(values){
    $.each(values, function (index, value) {
        $('#Values').append("<div class='Value' id='value_" + createFriendlyId(value.name) + "' title='inputs \"" + value.name + "\"'>" + value.name + ": " + value.value + "</div>");
    });
}

function renderFinalsCommands(FinalCommands){
    $.each(FinalCommands, function (index, value) {
        if (value.hotkey) {
            $('#Finals').append("<div class='FinalCommand' id='finalcommand_" + createFriendlyId(value.command) + "' data-command='" + value.command + "' title='inputs \"" + value.command + "\"' accesskey='" + value.hotkey + "'>" + value.displayedName + "<br />(alt+"+value.hotkey+")</div>");
        } else {
            $('#Finals').append("<div class='FinalCommand' id='finalcommand_" + createFriendlyId(value.command) + "' data-command='" + value.command + "' title='inputs \"" + value.command + "\"'>" + value.displayedName + "</div>");
        }
    });

    addCommand("#Finals");
}

function renderSuggestions(suggestions){

    var availableCommands = new Array();

    $.each(suggestions, function (index, value) {
        availableCommands.push(value.value);
    });

    availableCommands.push("abort");

    // need to rebind
    $("#input").autocomplete({
        source: availableCommands
    });
}

function renderProducts(products){
    $.each(products, function (index, value) {
        $('#Products').append("<div class='productline'>" +
        "                       <span class='name' title='" + value.description + "'>" + value.name + "</span>" +
        "                       <span class='amount'>" + value.amount + "</span>" +
        "                       <span class='individualPrice'>" + value.individualPrice + "</span>" +
        "                       <span class='accumulatedPrice'>" + value.accumulatedPrice + "</span>" +
        "                       <span class='addone' data-command='addone " + value.name + "'>[+]</span>" +
        "                       <span class='removeone' data-command='removeone " + value.name + "'>[-]</span>" +
        "                       <span class='removeall' data-command='removeall " + value.name + "'>[X]</span>" +
        "</div>");
    });

    $('.addone').click(function () {
        runCommand($(this).attr("data-command"));
    });

    $('.removeone').click(function () {
        runCommand($(this).attr("data-command"));
    });

    $('.removeall').click(function () {
        runCommand($(this).attr("data-command"));
    });
}

function renderInvolvedAccounts(involvedAccounts){
    $.each(involvedAccounts, function (index, value) {
        if (value.hotkey) {
            $('#InvolvedAccounts').append("<div class='InvolvedAccount' data-command='" + value.command + "' id='involvedaccount_" + createFriendlyId(value.command) + "' accesskey='" + value.hotkey + "'>" + value.displayedName + "<br />(alt+"+value.hotkey+")</div>");
        } else {
            $('#InvolvedAccounts').append("<div class='InvolvedAccount' data-command='" + value.command + "' id='involvedaccount_" + createFriendlyId(value.command) + "'>" + value.displayedName + "</div>");
        }
    });

    addCommand("#InvolvedAccounts");
}


function renderCommands(commands){

    $.each(commands, function (index, value) {
        $('#Commands').append("<div data-command='" + value.command + "' id='command_" + createFriendlyId(value.command) + "' " +
        "                       title='inputs \"" + value.command + "\"' " +
        ((value.styles) ?  "class='Command " + value.styles + "' " : "class='Command' ") +
        ((value.hotkey) ?  "accesskey='" + value.hotkey + "'>" : ">") +
        "                       " + value.displayedName +
        ((value.hotkey) ?  "<br />(alt+"+value.hotkey+")" : "") +
        ((value.fact1) ?  "<span class='CommandLabelLeft'>"+value.fact1+"</span>" : "") +
        ((value.fact2) ?  "<span class='CommandLabelRight'>"+value.fact2+"</span>" : "") +
        "</div>");
    });

    addCommand("#Commands");
}

function renderArbitraryHtml(arbitraryHtml){
    $.each(arbitraryHtml, function (index, value) {
        $('#arbitraryHtmlOutput').html("<div id='plugin_" + value.plugin + "'>" + value.code + "</div>");
    });
}

function renderBackgroundHtml(backgroundHtml){
    $.each(backgroundHtml, function (index, value) {
        $('#BackgroundHtml').html("<div id='background_" + createFriendlyId(value.plugin) + "'><span class='close' onclick='this.parentNode.remove();'>[close]</span>" + value.code + "</div>");
    });
}

function renderPages(pages){
    $.each(pages, function (index, value) {
        $('#Pages').append("<div class='page'>" + value.contents + "</div>");
    });
}

function renderAccountCommands(accountCommands){
    $.each(accountCommands, function (index, value) {
        $('#Accounts').append("<div class='Account' data-command='" + value.command + "' id='account_" + createFriendlyId(value.command) + "'>" + value.displayedName + "</div>");
    });

    addCommand("#Accounts");
 // TypeError: Cannot read property 'call' of undefined. <- suppressed. todo fix
}

function addCommand(elementseries){
    try {
        $.each($(elementseries).children().click(function () {
            runCommand($(this).attr("data-command"));
        }));
    } catch (e){ console.log(e)}
}

function renderHint(hint){
    $('#input')[0].placeholder = hint[0].hint;
}


function renderImages(Images){
    $.each(Images, function (index, value) {
        $('#Images').append("<img src='" + value.href + "' title='" + value.alt+ "' alt='" + value.alt+ "' /> ");
    });
}