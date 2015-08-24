/**
 * Created by ejonker on 20-1-2015.
 */

function runCommand(command){
    clearSyncType();
    localStorage.setItem('userinput', ""); // this forces a change. empty commands are not executed.
    localStorage.setItem('userinput', command);
}
