import { ReservasiInitializ } from "./managers/ReservasiInitializ";
import wsManager from "../../js/core/websocket-instance";

document.addEventListener('DOMContentLoaded', () => {
    const reservasiMAnager = new ReservasiInitializ();

    wsManager.on('cancel-reservasi', (result) => {
        reservasiMAnager.updateDataActivity(result.data.id);
        wsManager.send({
            event:"event:ack",
            data:{
                eventId: result.meta.eventId,
            }
        })
    });
});