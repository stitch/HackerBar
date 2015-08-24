/**
 * Created by ejonker on 22-1-2015.
 */


// listen for this event from other tabs, note that, this always happens from another tab, because this sends something to another screen.
window.addEventListener('storage', storageEvent, false);

$(document).ready(function () {
    renderAccountCommands(JSON.parse(localStorage.getItem("AccountCommand")));
});


function storageEvent(storageEvent){
    //console.log(storageEvent);

    // this for example happens when accounts are added.
    if (storageEvent.key == "AccountCommand"){
        clearUi();
        renderAccountCommands(JSON.parse(localStorage.getItem("AccountCommand")));
    }
}

