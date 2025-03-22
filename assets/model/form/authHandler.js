import { validateFileType } from "../utils/helper/helperFunctions";
import { checkAllFields, comparePasswords } from "./validateHandler";

export const registerUser = (userData) => {
  // Profilbild ist nicht notwendig
  const excludedKeys = ["pictureUpload"];
  // checken ob ein Wert null oder leer ist ausser Profilbild
  if (!checkAllFields(userData, userData.registerForm, excludedKeys)) {
    return;
  }

  // dann schonmal password und passwordRepeat vergleichen
  if (!comparePasswords(userData, userData.registerForm)) {
    return;
  }

  // Jetzt senden wir die Form ab
  userData.registerForm.submit();
};

export const loginUser = (userData) => {
  // checken ob ein Wert null ist
  if (!checkAllFields(userData, userData.loginForm)) {
    return;
  }

  // Jetzt senden wir die Form ab
  userData.loginForm.submit();
};

export const changeResetPassword = (userData) => {
  // checken ob ein Wert null ist
  if (!checkAllFields(userData, userData.changePasswordForm)) {
    return;
  }

  // dann schonmal password und passwordRepeat vergleichen
  if (!comparePasswords(userData, userData.changePasswordForm)) {
    return;
  }

  userData.changePasswordForm.submit();
};

export const checkUploadFile = (userData, event) => {
  const pictureUpload = event.target;
  // Erlaubte Dateitypen
  const allowedExtensions = ["image/jpeg", "image/gif", "image/png", "image/svg+xml"];

  validateFileType(allowedExtensions, pictureUpload);
};
