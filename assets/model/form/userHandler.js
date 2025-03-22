import { checkAllFields, comparePasswords } from "./validateHandler";

export const editUser = (userData) => {
  let excludedKeys = ["pictureUpload", "originalImageSrc"];
  // Password und Password wdh seperat, da kein Pflichtfeld mehr
  if (!userData.password.value && !userData.passwordRepeat.value) {
    excludedKeys.push("password", "passwordRepeat"); // Wird nicht mehr berücksichtigt
  } else {
    // Wenn ein Wert in password oder passwordRepeat vorhanden ist dann validieren
    if (!comparePasswords(userData, userData.editUserForm)) {
      return;
    }
  }

  // Validieren
  if (!checkAllFields(userData, userData.editUserForm, excludedKeys)) {
    return;
  }

  userData.editUserForm.submit();
};
