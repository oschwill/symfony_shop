import * as bootstrap from "bootstrap";
import {
  showLoadingSpinner,
  hideLoadingSpinner,
  animateProductCards,
} from "../utils/helper/helperFunctions";
// UI
import { loadingSpinner } from "../../view/partialUI";

// Hier rufen wir synchron auf da die Daten schon da sind
export const addPaginationListeners = () => {
  const paginationLinks = document.querySelectorAll(".pagination a.page-link");
  paginationLinks.forEach((link) => {
    // Nur einen Event Listener hinzufügen, wenn er noch nicht hinzugefügt wurde
    if (!link.hasAttribute("data-listener-attached")) {
      link.addEventListener("click", function (e) {
        e.preventDefault(); // Verhindert das Standardverhalten des Links

        const url = this.getAttribute("href");

        // Loading Element setten
        showLoadingSpinner(loadingSpinner);

        // Fetchen den Inhalt und injecten ihn ohne die Seite neu zu reloaden
        fetch(url)
          .then((response) => response.text())
          .then((html) => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");
            const newContent = doc.querySelector(".container");

            if (newContent) {
              document.querySelector(".container").innerHTML = newContent.innerHTML;
              window.history.pushState({}, "", url);

              animateProductCards();
              // Füge die Event-Listener erneut hinzu, nachdem der Inhalt aktualisiert wurde
              addPaginationListeners();
            }
          })
          .catch((error) => console.warn("Fehler beim Laden der Seite:", error))
          .finally(() => {
            hideLoadingSpinner(); // Spinner ausblenden
          });
      });

      // Markiere den Link als Listener-attached
      link.setAttribute("data-listener-attached", "true");
    }
  });
};

// Tooltip bauen
export const createToolTip = () => {
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
};

// Countdown sichtbar machen nach erfolgreicher Verifizierung
export const setCountDown = () => {
  const timerElement = document.getElementById("timer");
  let duration = parseInt(timerElement.textContent, 10);

  const intervalId = setInterval(function () {
    duration--;
    timerElement.textContent = duration;

    if (duration <= 0) {
      clearInterval(intervalId);
      // Weiterleiten an de Login Page
      window.location.href = "/login?verified=true";
    }
  }, 1000);
};
