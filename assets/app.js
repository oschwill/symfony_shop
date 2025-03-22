/**
 * CONTROLLER *
 */

//## start the Stimulus application ##//
/** CSS **/
// import "./bootstrap";
import * as bootstrap from "bootstrap";
import "bootstrap-icons/font/bootstrap-icons.css"; // icons
import "./styles/app.css";
/* Handler */
import { handleEvent } from "./model/eventHandler/generalEventHandler";
/* LOADING */
import {
  showLoadingSpinner,
  hideLoadingSpinner,
  animateProductCards,
} from "./model/utils/helper/helperFunctions";

import { loadingSpinner } from "./view/partialUI";
/*Plugins */
// import { swiper, swiper2 } from "./plugin/mySwiper"; // Swiper Hyper Dyper dooo
import "fslightbox";
import {
  addPaginationListeners,
  createToolTip,
  setCountDown,
} from "./model/loadedDomInteractions/loadedDomHandlerFunctions";

const eventTypes = ["click", "change", "submit", "input", "mousemove"]; // to be continued

// Wir bauen uns einen asynchronen Eventhandler Wrapper
const asyncEventHandlerWrapper = (handler) => {
  return async (event) => {
    try {
      await handler(event);
    } catch (error) {
      console.error("Fehler im Event-Handler:", error); //
    }
  };
};

/**
 * ****
 ***  Event-Delegation für alle Dokumente ***
 */

// Alle Trigger Events!!!!
eventTypes.forEach((eventType) => {
  document.addEventListener(
    eventType,
    asyncEventHandlerWrapper(async (event) => {
      const currentRoute = `/${window.location.pathname.split("/")[1]}`; // Aktuellen ersten Route Param holen

      await handleEvent(event, currentRoute);
    })
  );
});

// Alle anderen Events die keinen direkten Trigger haben, behandeln wir separat in onLoadedEventHandler => Refactorn wir am Schluß
document.addEventListener("DOMContentLoaded", function () {
  showLoadingSpinner(loadingSpinner); // spinner einblenden
  const currentRoute = window.location.pathname;
  const gradientBG = document.querySelector("#bgcustom_shit");

  if (currentRoute.includes("/verify")) {
    setCountDown();
  } else if (currentRoute.includes("/product")) {
    createToolTip();
  } else if (currentRoute.includes("/shop")) {
    animateProductCards();
    addPaginationListeners();
  }

  const onMouseMoveFN = (event) => {
    gradientBG.style.backgroundImage =
      "radial-gradient(at " +
      event.clientX +
      "px " +
      event.clientY +
      "px, rgba(255, 255, 255, 0.1) 0%, rgba(109, 106, 106, 0.75) 70%)";
  };

  document.addEventListener("mousemove", onMouseMoveFN);
});

window.addEventListener("load", function () {
  hideLoadingSpinner(); // Spinner ausblenden
});
