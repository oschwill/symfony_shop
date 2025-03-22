import { removeFileErrorPopup, showFileErrorPopup } from "../../../view/partialUI";

export const isValidEmail = (email) => {
  // Einfache Regex für die E-Mail-Validierung
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

export const setCountDown = ({
  countdownMessage,
  sendResetPasswordEmailButton,
  emailForPasswordReset,
}) => {
  // Disabelen der Felder Buttons what ever
  sendResetPasswordEmailButton.disabled = true;
  emailForPasswordReset.disabled = true;

  // Countdown starten (60 Sekunden)
  let countdown = 60;
  countdownMessage.textContent = `Wartezeit: ${countdown} Sekunden`;

  const interval = setInterval(() => {
    countdown--;
    countdownMessage.textContent = `Wartezeit: ${countdown} Sekunden`;

    if (countdown <= 0) {
      clearInterval(interval);
      sendResetPasswordEmailButton.disabled = false;
      emailForPasswordReset.disabled = false;
      countdownMessage.textContent = ""; // Entfernt die Nachricht nach Ablauf
    }
  }, 1000);
};

// Hier injecten wir den LoadingSpinner in den Dom
export const showLoadingSpinner = (loadingSpinner) => {
  const spinnerHTML = loadingSpinner();
  const container = document.createElement("div");
  container.innerHTML = spinnerHTML; // dangerouslySetInnerHTML incoming...
  document.body.appendChild(container.firstChild);
};

// Hier entfernen wir wieder den LoadingSpinner
export const hideLoadingSpinner = () => {
  const spinner = document.getElementById("loadingSpinner");
  if (spinner) {
    spinner.remove();
  }
};

// Die Produkte sollen animiert erscheinen
export const animateProductCards = () => {
  const productCards = document.querySelectorAll(".card");

  productCards.forEach((card, index) => {
    setTimeout(() => {
      card.classList.add("show");
    }, index * 100); // Verzögert die Animation für jede Karte
  });
};

export const checkFilledProductImageFields = (inputs) => {
  const pictureInputs = document.querySelectorAll("#product_pictures input[type='text']"); // Hole immer die neuesten Felder
  return Array.from(pictureInputs).every((input, index) => {
    if (index < pictureInputs.length && !input.value.trim()) {
      input.style.border = "2px solid red";
      return false;
    } else {
      input.style.border = ""; // Entferne den roten Rahmen
      return true;
    }
  });
};

export const isValidUrl = (url, isImage = false) => {
  const urlPattern = new RegExp(
    "^(https?:\\/\\/)?" +
      "((([a-zA-Z\\d]([a-zA-Z\\d-]*[a-zA-Z\\d])*)\\.)+[a-zA-Z]{2,}|" + // Domainname
      "((\\d{1,3}\\.){3}\\d{1,3}))" +
      "(\\:\\d+)?(\\/[-a-zA-Z\\d%_.~+]*)*" +
      "(\\?[;&a-zA-Z\\d%_.~+=-]*)?" +
      "(\\#[-a-zA-Z\\d_]*)?$",
    "i"
  );

  // Gültige URL?
  const isValid = urlPattern.test(url);

  // Prüfen ob es ein Image ist falls gewollt
  if (isImage) {
    return isValid && isValidImageUrl(url);
  }

  return isValid;
};

const isValidImageUrl = (url) => {
  const cleanUrl = url.split("?")[0]; // Parameter entfernen
  return /\.(jpg|jpeg|png|gif|bmp|webp)$/i.test(cleanUrl);
};

export const showPopup = ({ popup }) => {
  if (popup) {
    // Popup von rechts einblenden
    popup.style.right = "20px";
  }
};

export const hidePopup = ({ popup }) => {
  if (popup) {
    popup.style.right = "-350px"; // Popup wieder verschwinden lassen

    // Warten bis das Popup aus dem Sichtbereich ist
    setTimeout(() => {
      // do Nothing...
    }, 500);
  }
};

export const isLastParamNumeric = () => {
  const path = window.location.pathname;
  const segments = path.split("/");
  // letzte Segment holn
  const lastSegment = segments[segments.length - 1];
  // Überprüfen ob das letzte Segment numerisch ist!
  const isNumeric = !isNaN(lastSegment) && lastSegment.trim() !== "";
  return isNumeric;
};

export const getUrlId = () => {
  const url = window.location.href;
  const productId = url.split("/").pop();
  return productId;
};

export const animateElement = (container, animType, animationDuration = "0.3s") => {
  if (!container) {
    return;
  }

  container.classList.remove("animate__animated", animType);
  // Reflowen
  void container.offsetWidth;
  container.classList.add("animate__animated", animType);
  container.style.animationDuration = animationDuration;
};

export const validateFileType = (allowedExtensions, input) => {
  const file = input.files[0];

  if (file) {
    const fileType = file.type;

    // Überprüfen ob Dateityp erlaubt ist
    if (!allowedExtensions.includes(fileType)) {
      // Error Melduing via Popup shown
      showFileErrorPopup(input, "Nur JPG, GIF, SVG und PNG Dateien sind erlaubt.");

      // Upload Feld clearn falls falscher Dateityp
      input.value = "";
    } else {
      removeFileErrorPopup(input);
    }

    return true;
  }

  removeFileErrorPopup(input);

  return false;
};
