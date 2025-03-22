import { deletePictureButton } from "../../view/partialUI";
import {
  checkFilledProductImageFields,
  hidePopup,
  isLastParamNumeric,
  isValidUrl,
  showPopup,
} from "../utils/helper/helperFunctions";
import * as bootstrap from "bootstrap";

export const limitPriceField = (event) => {
  let value = event.target.value;
  // Erlaubt nur Zahlen und maximal zwei Dezimalstellen
  value = value.replace(/[^0-9.]/g, ""); // Erlaubt nur Zahlen und Punkt
  const parts = value.split(".");
  if (parts.length > 2) {
    value = parts.shift() + "." + parts.join(""); // Entfernt zusätzliche Punkte
  }
  if (parts[1] && parts[1].length > 2) {
    value = parts[0] + "." + parts[1].slice(0, 2); // Beschränkt auf zwei Dezimalstellen
  }
  event.target.value = value;
};

export const validatePictureUrls = (elements, pictureInputs) => {
  let validCount = 0;

  pictureInputs.forEach((input) => {
    const url = input.value;

    updateImagePreview(input, isLastParamNumeric()); // Das Bild daneben injecten

    // Überprüfen ob die URL gültig ist
    if (isValidUrl(url, true)) {
      // Entfernt Fehlermarkierungen und Tooltip bei gültiger URL
      input.classList.remove("is-invalid");
      input.classList.add("is-valid");
      input.removeAttribute("data-bs-toggle");
      input.removeAttribute("title");

      // Entferne Tooltip falls es aktiv ist
      const tooltipInstance = bootstrap.Tooltip.getInstance(input);
      if (tooltipInstance) {
        tooltipInstance.dispose();
      }

      validCount++;

      // Falls Rahmen vorhanden ist:
      checkFilledProductImageFields(pictureInputs);
    } else {
      // console.log("Ungültige URL:", url); // Debugging-Ausgabe

      if (input.value !== "") {
        // Setzt das Input-Feld als ungültig und aktiviert das Tooltip
        input.classList.remove("is-valid");
        input.classList.add("is-invalid");
        input.setAttribute("data-bs-toggle", "tooltip");
        input.setAttribute("title", "Ungültige Image URL");

        // Initialisiere und zeige das Tooltip manuell
        let tooltipInstance = bootstrap.Tooltip.getInstance(input);
        if (!tooltipInstance) {
          tooltipInstance = new bootstrap.Tooltip(input, {
            trigger: "manual", // Tooltip manuell anzeigen
            placement: "top",
          });
        }
        tooltipInstance.show(); // Zeigt das Tooltip sofort an
      }
    }
  });

  const minPicturesValid = validCount >= 3;

  // Aktiviert oder deaktiviert den Submit-Button basierend auf der Validität
  toggleSubmitButton(elements, minPicturesValid);
};

const toggleSubmitButton = (elements, minPicturesValid) => {
  const titleField = elements.title;
  const priceField = elements.price;
  const descriptionField = elements.description;

  // Sind alle Felder ausgefüllt und gültig
  const titleValid = titleField.value.trim() !== "";
  const priceValid = priceField.value.trim() !== "" && !isNaN(priceField.value);
  const descriptionValid = descriptionField.value.trim() !== "";

  // Hamwa alle Bildas!?
  const formValid = titleValid && priceValid && descriptionValid && minPicturesValid;

  // Wir holen uns den Submit Button aus der Form
  const formFields = Array.from(elements.productForm.elements);
  const submitButton = formFields.find((el) => el.id === "submit_button");

  // Aktiviert oder deaktiviert den Submit-Button
  submitButton.disabled = !formValid;

  formValid ? showPopup(elements) : hidePopup(elements);
};

// Das Bild dirket erscheinen lassen wenn es gepastet wird
const updateImagePreview = (input, isOnEdit) => {
  if (!isOnEdit) {
    return;
  }

  let imgElement = input.nextElementSibling;

  // Falls kein Bild-Element existiert, erstelle ein neues und füge es nach dem Input-Feld ein
  if (!imgElement || imgElement.tagName.toLowerCase() !== "img") {
    imgElement = document.createElement("img");
    imgElement.style.width = "100px"; // Setze die Breite
    imgElement.style.height = "100px"; // Setze die Höhe
    imgElement.classList.add("img-thumbnail", "ms-2");
    input.parentNode.insertBefore(deletePictureButton(), input.nextSibling);
    input.parentNode.insertBefore(imgElement, input.nextSibling);
  }

  const url = input.value;

  //  URL gültig ?
  if (isValidUrl(url, true)) {
    // Bild mit URL
    imgElement.src = url;
    imgElement.alt = "Produktbild";
    imgElement.style.display = "block";
  } else {
    imgElement.src = "";
    imgElement.alt = "Kein Bild"; // Alternativtext
    imgElement.style.display = "none"; // Oder lieber verstecken?k
  }
};
