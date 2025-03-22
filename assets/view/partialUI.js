import * as bootstrap from "bootstrap";
// IMAGES
import noPicture from "../images/no_picture.jpg";
// Loading Spinner Element
export const loadingSpinner = () => {
  return `<div id="loadingSpinner">
    <img src="/build/images/loadingSpinner.gif" alt="Loading..." />
  </div>;`;
};

export const showSuccessModal = (successType, productId) => {
  // Erstmal alle offenen Modals schließen
  document.querySelectorAll(".modal.show").forEach((modalElement) => {
    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) {
      modal.hide();
    }
  });

  let successMessage;
  let successTitle;
  switch (successType) {
    case "create_product_success":
      successTitle = "Produkt erstellt";
      successMessage = "Das Produkt wurde erfolgreich erstellt.";
      break;
    case "delete_product_success":
      successTitle = "Produkt gelöscht";
      successMessage = "Das Produkt wurde erfolgreich gelöscht.";
      break;
    case "update_product_success":
      successTitle = "Produkt editiert";
      successMessage = "Das Produkt wurde erfolgreich editiert.";
      break;
    default:
      successMessage = "Jo whatz uuuuuuuup joooo.";
  }

  const footerBox = successBoxPartial(successType, productId);

  const modalHtml = `
    <div class="modal fade text-dark" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title" id="successModalLabel">${successTitle}</h4>
          </div>
          <div class="modal-body">
            <div class="d-flex align-items-center gap-2">
              <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130.2 130.2" width="20" height="20">
                  <circle class="path circle" fill="none" stroke="#198754" stroke-width="6" stroke-miterlimit="10" cx="65.1" cy="65.1" r="62.1" />
                  <polyline class="path check" fill="none" stroke="#198754" stroke-width="6" stroke-linecap="round" stroke-miterlimit="10" points="100.2,40.2 51.5,88.8 29.8,67.5" />
              </svg>
              <span class="fs-5">${successMessage}</span>
            </div>
          </div>
          <div class="modal-footer">
            ${footerBox}
          </div>
        </div>
      </div>
    </div>
  `;
  document.body.insertAdjacentHTML("beforeend", modalHtml);
  const successModal = new bootstrap.Modal(document.getElementById("successModal"));
  successModal.show();
};

export const showErrorModal = (errorType) => {
  // Erstmal alle offenen Modals schließen
  document.querySelectorAll(".modal.show").forEach((modalElement) => {
    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) {
      modal.hide();
    }
  });

  let errorMessage;
  switch (errorType) {
    case "create_product_error":
      errorMessage = "Es gab einen Fehler beim Erstellen des Produkts.";
      break;
    case "delete_product_error":
      errorMessage = "Es gab einen Fehler beim Löschen des Produkts.";
      break;
    case "update_product_error":
      errorMessage = "Es gab einen Fehler beim Aktualisieren des Produkts.";
      break;
    default:
      errorMessage = "Es ist ein unbekannter Fehler aufgetreten.";
  }

  const modalHtml = `
    <div class="modal fade text-dark" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title" id="errorModalLabel">Fehler</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="d-flex align-items-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill text-danger flex-shrink-0 me-2" viewBox="0 0 16 16" aria-label="Error:">
                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
              </svg>
              <span class="fs-5">${errorMessage}. Bitte versuchen Sie es erneut.</span>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
          </div>
        </div>
      </div>
    </div>
  `;
  document.body.insertAdjacentHTML("beforeend", modalHtml);
  const errorModal = new bootstrap.Modal(document.getElementById("errorModal"));
  errorModal.show();
};

const successBoxPartial = (successType, productId) => {
  let buttonBar;
  switch (successType) {
    case "create_product_success":
      buttonBar = `
                  <a href="/product/insert" class="btn btn-secondary">Neues Produkt erstellen</a>
                  <a href="/product/${productId}" class="btn btn-primary">Zum Produkt</a>
                `;
      break;
    case "delete_product_success":
      buttonBar = `
                  <a href="/shop" class="btn btn-primary">OK</a>
                 `;
      break;
    case "update_product_success":
      buttonBar = `
                  <a href="/shop" class="btn btn-secondary">Zurück zum Shop</a>
                  <a href="/product/${productId}" class="btn btn-primary">Zum aktualisierten Produkt</a>
                `;
      break;
    default:
      buttonBar = null;
  }

  return buttonBar;
};

export const showSearchResults = (results, elements) => {
  const showResults = elements.showResults;

  // Inhalte imemr wiede rentfernen
  showResults.innerHTML = "";

  // Neues u list erzeugen
  const ul = document.createElement("ul");
  ul.classList.add("list-group");

  // Search Result Liste bauen
  if (results.length > 0) {
    results.forEach((item) => {
      const li = document.createElement("li");
      li.classList.add(
        "list-group-item",
        "d-flex",
        "align-items-center",
        "gap-2",
        "search-result-li"
      );

      // Link erstellen und hinzufügen, der das gesamte <li> umschließt
      const link = document.createElement("a");
      link.href = `/product/${item.product.id}`; // Dynamischer Link zur Produktseite
      link.classList.add("d-flex", "align-items-center", "text-decoration-none", "w-100"); // Text ohne Unterstreichung

      link.style.color = "inherit";
      // Bild erstellen und hinzufügen
      const img = document.createElement("img");
      img.src = item.picture ? item.picture.picturePath : noPicture;
      img.alt = item.product.title;
      img.style.width = "50px";
      img.style.height = "50px";
      img.style.marginRight = "10px";

      // Produkttitel erstellen
      const title = document.createElement("span");
      title.textContent = item.product.title;
      title.classList.add("ms-3");

      // Bild und Titel in den Link einfügen
      link.appendChild(img);
      link.appendChild(title);

      // Link in <li> einfügen
      li.appendChild(link);

      // <li> in das <ul> einfügen
      ul.appendChild(li);
    });
  } else {
    // Keine Ergebnisse
    const noResultsItem = document.createElement("li");
    noResultsItem.classList.add("list-group-item");
    noResultsItem.textContent = "Keine Treffer";
    ul.appendChild(noResultsItem);
  }

  // und zu guter letzt geben wir et aus
  showResults.appendChild(ul);
};

export const clearSearchResults = (elements) => {
  const showResults = elements.showResults;

  // Search Liste clearn
  showResults.innerHTML = "";
};

export const deletePictureButton = () => {
  // Erstelle ein Button-Element
  const button = document.createElement("button");
  button.type = "button";
  button.className = "btn btn-danger btn-sm ms-2 remove-picture";
  button.setAttribute("data-id", "none");
  button.textContent = "Löschen"; // Button-Text

  return button; // Rückgabe des Button-Elements
};

export const showFileErrorPopup = (input, message) => {
  let existingTooltip = input.parentNode.querySelector(".custom-tooltip");
  if (existingTooltip) {
    existingTooltip.remove();
  }

  // Tooltip Container erstellen
  const tooltip = document.createElement("div");
  tooltip.classList.add("custom-tooltip");
  tooltip.innerHTML = `<span class="tooltip-text">${message}</span>`;

  // und positionieren
  input.parentNode.style.position = "relative";
  tooltip.style.position = "absolute";
  tooltip.style.top = "70%";
  tooltip.style.right = "-240px";
  tooltip.style.transform = "translateY(-50%)";

  // Tooltip hinzufügen
  input.parentNode.appendChild(tooltip);

  // Tooltip nach 5 Sekunden automatisch entfernen lassen?
  // setTimeout(() => {
  //   tooltip.remove();
  // }, 5000);
};

export const removeFileErrorPopup = (input) => {
  let existingTooltip = input.parentNode.querySelector(".custom-tooltip");
  if (existingTooltip) {
    existingTooltip.remove();
  }
};
