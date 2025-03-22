import { swiper2 } from "../../plugin/mySwiper";
import {
  editUserDataElements,
  insertProductElements,
  loginUserElements,
  passwordResetElements,
  registerUserElements,
  searchProductElements,
} from "../../view/baseElements";
import { sendForgotPasswordRequest } from "../api/fetchData";
import { changeResetPassword, checkUploadFile, loginUser, registerUser } from "../form/authHandler";
import {
  createProduct,
  deleteProductPicture,
  killProduct,
  resetEditProduct,
  searchProduct,
  toggleEditProductForm,
  validateProductForm,
} from "../form/productHandler";
import { editUser } from "../form/userHandler";
import {
  createNewUrlInputField,
  resetUploadImage,
  showUploadPicture,
  synchronizeFSLightBoxWithSwiper,
  togglePasswordVisibility,
} from "../interactions/generalInteractions";
import { hidePopup } from "../utils/helper/helperFunctions";

const onAllRoutesIds = ["searchProduct"];
// Mappings für Events über die Routen / Noch auslagern!!
const eventRouting = {
  "/": {
    input: {
      "#searchProduct": searchProduct,
    },
    elements: searchProductElements,
  },
  "/login": {
    click: {
      "#toggle-password-visibility": togglePasswordVisibility,
      "#sendResetPasswordEmail": async function (elements) {
        await sendForgotPasswordRequest(elements);
      },
      'button[login-form-field="submitButton"]': function (elements) {
        // Erzeuge und löse einen `submit`-Event manuell aus
        const submitEvent = new Event("submit", { bubbles: true, cancelable: true });
        if (elements.loginForm.dispatchEvent(submitEvent)) {
          // Falls das Formular-Event nicht abgebrochen wird, kann das Formular gesendet werden
          elements.loginForm.submit();
        }
      },
    },
    change: {
      // Event-Handler für change Events
    },
    submit: {
      'form[name="login_form"]': loginUser,
    },
    elements: loginUserElements,
  },
  "/register": {
    click: {
      ".toggle-password-visibility": togglePasswordVisibility,
      'button[register-form-field="submitButton"]': function (elements) {
        // Erzeuge und löse einen submit-Event manuell aus
        const submitEvent = new Event("submit", { bubbles: true, cancelable: true });
        if (elements.registerForm.dispatchEvent(submitEvent)) {
          // Falls das Formular-Event nicht abgebrochen wird, kann das Formular gesendet werden
          elements.registerForm.submit();
        }
      },
    },
    submit: {
      'form[name="registration_form"]': registerUser,
    },
    change: {
      "#registration_form_pictureUpload": checkUploadFile,
    },
    elements: registerUserElements,
  },
  "/change_password": {
    click: {
      ".toggle-password-visibility": togglePasswordVisibility,
      'button[changepassword-form-field="submitButton"]': function (elements) {
        // Erzeuge und löse einen Event manuell aus
        const submitEvent = new Event("submit", { bubbles: true, cancelable: true });
        if (elements.changePasswordForm.dispatchEvent(submitEvent)) {
          // Falls das Formular Event nicht abgebrochen wird, kann das Formular gesendet werden
          elements.changePasswordForm.submit();
        }
      },
    },
    submit: {
      'form[name="password_reset_form"]': changeResetPassword,
    },
    elements: passwordResetElements,
  },
  "/user": {
    click: {
      ".toggle-password-visibility": togglePasswordVisibility,
      "#resetButton": resetUploadImage,
      'button[user-form-field="submitButton"]': function (elements) {
        // Erzeuge und löse einen Event manuell aus
        const submitEvent = new Event("submit", { bubbles: true, cancelable: true });
        if (elements.changePasswordForm.dispatchEvent(submitEvent)) {
          // Falls das Formular Event nicht abgebrochen wird, kann das Formular gesendet werden
          elements.changePasswordForm.submit();
        }
      },
    },
    submit: {
      'form[name="edit_user_form"]': editUser,
    },
    change: {
      "#edit_user_form_pictureUpload": showUploadPicture,
    },
    elements: editUserDataElements,
  },
  "/product": {
    click: {
      // Event-Handler für fsLightboxInit hinzufügen
      ".fslightbox-slide-btn": synchronizeFSLightBoxWithSwiper,
      "#add_product_picture": createNewUrlInputField,
      "#close-popup": hidePopup,
      "#confirmSubmit": createProduct,
      "#delete_product": killProduct,
      ".showProductEditForm": toggleEditProductForm,
      ".remove-picture": deleteProductPicture,
      ".resetEditForm": resetEditProduct,
    },
    input: {
      "#product_form_price": validateProductForm,
      '[product-form-field="title"]': validateProductForm,
      '[product-form-field="price"]': validateProductForm,
      '[product-form-field="description"]': validateProductForm,
      'input[id^="product_form_productPictures_"][id$="_picturePath"]': validateProductForm,
    },
    elements: insertProductElements,
  },
};

// Event Type Mapping
const getEventHandlersForRoute = (route) => ({
  click: eventRouting[route]?.click || {},
  change: eventRouting[route]?.change || {},
  submit: eventRouting[route]?.submit || {},
  input: eventRouting[route]?.input || {},
});

export const handleEvent = async (event, route) => {
  let allRoutes = handlerForAllRoutes(event);
  route = allRoutes ? "/" : route;
  // Die jeweiligen elemnts für die Route holen
  const { elements } = eventRouting[route] || {};
  // uns die Eventypes anhand der Route holen
  const eventHandlers = getEventHandlersForRoute(route);
  // Und nun holen wir uns den korrekten Event Type
  const handlers = eventHandlers[event.type] || {};

  if (!handlers) return;

  // Durch Iterieren und nach dem richtigen Handler suchen
  for (const [selector, handler] of Object.entries(handlers)) {
    // Wenn nun der Trigger hier matcht führen wir den jeweiligen Handler aus
    if (event.target.matches(selector) || event.target.closest(selector)) {
      event.preventDefault();

      // Prüfen ob der Handler eine asynchrone Funktion ist oder halt nicht
      if (handler instanceof Function && handler.constructor.name === "AsyncFunction") {
        await handler(elements);
      } else {
        // Einfacher Funktionsaufruf für synchrone Funktionen
        handler(elements, event); // event ist optional
      }
      // und dann raus aus der Schleife
      break;
    }
  }
};

export const handlerForAllRoutes = (event) => {
  if (onAllRoutesIds.includes(event.target.id)) {
    return true;
  }

  return false;
};
