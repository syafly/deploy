import { AbsensiInitializ } from "./managers/AbsensiInitializ";
import { TimeSettingsManager } from "./managers/TimeSettingsManager";

document.addEventListener('DOMContentLoaded', () => {
    new AbsensiInitializ();
    new TimeSettingsManager();
});