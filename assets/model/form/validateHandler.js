import { ErrorHandler } from "../utils/class/ErrorHandler";

const getFormFields = (userData, form) => {
  // Filtere nur die Felder die sich innerhalb des übergebenen Formulars befinden
  return Object.keys(userData).filter((key) => {
    const element = userData[key];
    // Wir holen uns alle Felder innerhalb des übergebenen forms, um sie später mit den Userdata Object abzugleich
    return element instanceof HTMLInputElement ||
      element instanceof HTMLTextAreaElement ||
      element instanceof HTMLSelectElement
      ? element.closest("form") === form
      : false;
  });
};

const validateFormFields = (userData, form, exclude = []) => {
  // Holen uns die relevanten Felder aus dem userData
  const formFields = getFormFields(userData, form);

  // Filtere nur die leeren Felder, der exclude is optional und wird nur dann ignoriert wenn er übergeben wird
  const nullFields = formFields.filter(
    (key) => !exclude.includes(key) && (userData[key].value === "" || userData[key].value === null)
  );

  return nullFields;
};

const errorStylingField = (nullFields, userData) => {
  console.log(userData);
  // vorher wieder die ErrorKlasse removen
  Object.keys(userData).forEach((key) => {
    const element = userData[key];

    if (element && element instanceof HTMLElement) {
      element.classList.remove("error-field");
    }
  });

  nullFields.forEach((field) => {
    const element = userData[field];
    if (element && element instanceof HTMLElement) {
      // Fügen die Klasse mit roten Rahmen an :)
      element.classList.add("error-field");
    }
  });
};

export const checkAllFields = (userData, form, exclude = []) => {
  const nullFields = validateFormFields(userData, form, exclude);

  if (nullFields.length > 0) {
    // Allgemeinen Error werfen
    ErrorHandler.showError(
      form,
      "Bitte füllen Sie alle Pflichtfelder aus",
      ".form-error-container"
    );
    // Fields markieren
    errorStylingField(nullFields, userData);
    return false;
  }

  return true;
};

export const comparePasswords = (userData, form) => {
  if (userData.password.value !== userData.passwordRepeat.value) {
    // Password field Error werfen
    ErrorHandler.showError(
      form,
      "Passwort und Passwort wiederholen müssen identisch sein",
      ".form-error-container"
    );
    // passwordRepeat markieren
    errorStylingField(
      [Object.keys(userData).find((key) => userData[key] === userData.passwordRepeat)],
      userData
    );

    return false;
  }

  return true;
};
