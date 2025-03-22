import { swiper2 } from "../../plugin/mySwiper"; // Swiper Hyper Dyper dooo
import {
  animateElement,
  checkFilledProductImageFields,
  validateFileType,
} from "../utils/helper/helperFunctions";

export const togglePasswordVisibility = (elements, event) => {
  const srcElement = event.srcElement;

  // Um welches eye-icon handelt es sich?
  switch (srcElement.id) {
    case "eye-icon-pw":
      toggleVisibility(elements.password, srcElement);
      break;
    case "eye-icon-pwwdh":
      toggleVisibility(elements.passwordRepeat, srcElement);
      break;
    case "eye-icon":
      toggleVisibility(elements.password, srcElement);
      break;

    default:
      console.warn("Unbekanntes Augen-Icon angeklickt. 🦹");
      break;
  }
};

const toggleVisibility = (passwordElement, iconElement) => {
  if (passwordElement.type === "password") {
    passwordElement.type = "text";
    iconElement.classList.remove("bi-eye");
    iconElement.classList.add("bi-eye-slash");
  } else {
    passwordElement.type = "password";
    iconElement.classList.remove("bi-eye-slash");
    iconElement.classList.add("bi-eye");
  }
};

export const showUploadPicture = (elements, event) => {
  // Ersmtal überprüfen ob richtiger Dateityp
  const allowedExtensions = ["image/jpeg", "image/gif", "image/png", "image/svg+xml"];

  if (!validateFileType(allowedExtensions, elements.pictureUpload)) {
    return;
  }

  const file = elements.pictureUpload.files[0];
  if (file) {
    const reader = new FileReader(); // holen uns de riehdah
    reader.onload = (e) => {
      elements.profileImage.src = e.target.result; // base 64 codierte Darstellung unserer File in result schieben wir in unser img src
    };

    reader.readAsDataURL(file); // und nun lösen wir unser onload Event aus...
    // animieren
    animateElement(elements.profileImage, "animate__zoomIn");

    // Reset Button anzeigen
    resetButtonContainer.style.display = "block";
    animateElement(resetButtonContainer, "animate__fadeIn");
  }
};

export const resetUploadImage = (elements) => {
  elements.profileImage.src = elements.originalImageSrc; // Setzten wieder unser ursprüngliches Bild ein
  elements.pictureUpload.value = ""; // Der Datei Upload wird gecleart
  elements.resetButtonContainer.style.display = "none"; // und der Resett Button verschwindet wieder
};

export const synchronizeFSLightBoxWithSwiper = () => {
  const currentFSIndex = fsLightboxInstances["gallery"].stageIndexes.current;
  swiper2.slideTo(currentFSIndex);
};

export const createNewUrlInputField = (elements) => {
  const { productPicturesContainer, maxPictures, addButton } = elements;

  const prototype = productPicturesContainer
    ? productPicturesContainer.getAttribute("data-prototype")
    : null;

  const inputs = productPicturesContainer.querySelectorAll('input[type="text"]');

  const currentCount = inputs.length;

  let allFilled = checkFilledProductImageFields(inputs);

  if (allFilled && currentCount < maxPictures) {
    const index = productPicturesContainer.children.length;
    const newPicture = prototype.replace(/__name__/g, index);
    productPicturesContainer.insertAdjacentHTML("beforeend", newPicture);

    // Animieren, aber vorher die DOM Referenz holn
    const newInputField = productPicturesContainer.lastElementChild;
    animateElement(newInputField, "animate__fadeIn");

    if (currentCount + 1 === maxPictures) {
      addButton.style.display = "none"; // Deaktiviert den Button
    }
  }
};
