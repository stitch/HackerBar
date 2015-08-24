/**
 * Created by ejonker on 20-1-2015.
 */

// listen for events from other pages/tabs. There is no way that you can listen to changes on the current tab.
// the reason is probably a design mistake... because why treat the local tab not as a tab?
window.addEventListener('storage', onStorageEvent, false);

function onStorageEvent(storageEvent, data) {
    // console.log(storageEvent, data);
    if (storageEvent.key == "userinput" && storageEvent.newValue != "") {
        runCommand(storageEvent.newValue);
    }
};


function runCommand(inputCommand) {
    clearSyncType();

    if (inputCommand == ""){return;} // no empty input.
    if(inputCommand == "abort"){ $('#arbitraryHtmlOutput').empty(); } // clear out the arbitrary html that was downloaded... (instant memory management)

    $('#input').attr('readonly', false).css("background-color", "#777777");
    $('#input')[0].value = "";
    $('#input')[0].placeholder = "Loading...";

    $.ajax({
        type: "POST",
        dataType: "json",
        url: 'HackerBar.php',
        data: "input=" + (inputCommand ? inputCommand : ""),
        success: function (result, data) {
            $('#input').attr('readonly', false).css("background-color", "#F7F7F7");
            $('#input')[0].placeholder = "Enter command...";
            focusInput();
            renderOutput(result);
            storeLocally(result);
        }
    });
}

