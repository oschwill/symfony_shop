/**
 * Holt die Formularfelder basierend auf dem Attribut und gibt ein Objekt mit den Feldern und Fromnamen zurück.
 */
const getFormElements = (formName, fieldAttribute, formIdentifier = "form") => {
  const form = document.querySelector(`form[name="${formName}"]`);

  if (!form) {
    console.warn(`Formular mit Namen "${formName}" wurde nicht gefunden.`);
    return null;
  }

  const elements = form.querySelectorAll(`[${fieldAttribute}]`);

  const formFields = Array.from(elements).reduce((acc, element) => {
    const name = element.getAttribute(fieldAttribute);
    acc[name] = element;
    return acc;
  }, {});

  return { [formIdentifier]: form, ...formFields };
};

const additionalLoginElements = () => {
  const sendResetPasswordEmailButton = document.querySelector("#sendResetPasswordEmail");
  const countdownMessage = document.querySelector("#countdownMessage");

  return { sendResetPasswordEmailButton, countdownMessage };
};

const additionalEditUserElements = () => {
  const profileImage = document.querySelector("#profileImage");
  const resetButton = document.querySelector("#resetButton");
  const resetButtonContainer = document.querySelector("#resetButtonContainer");
  const originalImageSrc = profileImage?.src; // Holt sich das aktuelle Bild das gezeigt wird

  return { profileImage, resetButton, resetButtonContainer, originalImageSrc };
};

const additionalInsertProductElements = () => {
  const addButton = document.querySelector("#add_product_picture");
  let productPicturesContainer = document.querySelector("#product_form_productPictures");
  const maxPictures = 8;
  const popup = document.getElementById("success-popup");

  return { productPicturesContainer, maxPictures, addButton, popup };
};

const additionalDeleteProductElements = () => {
  const productId = document.querySelector("#productId");

  return { productId };
};

const additionalEditProductElements = () => {
  const formContainer = document.querySelector(".editForm");
  const productDataElement = document.getElementById("productData");
  let originalValues = "";

  if (productDataElement) {
    // Escape Strings müssen entfernt werden!!!!
    originalValues = {
      title: productDataElement?.dataset.title.replace(/\\u[\dA-F]{4}/gi, function (match) {
        return String.fromCharCode(parseInt(match.replace(/\\u/g, ""), 16));
      }),
      price: productDataElement?.dataset.price,
      description: productDataElement?.dataset.description.replace(
        /\\u[\dA-F]{4}/gi,
        function (match) {
          return String.fromCharCode(parseInt(match.replace(/\\u/g, ""), 16));
        }
      ),
      pictures: JSON.parse(productDataElement?.dataset.pictures),
    };
  }

  return { formContainer, originalValues };
};

const searchProductFormElements = () => {
  const input = document.querySelector("#searchProduct");
  const showResults = document.querySelector(".show-search-results");

  return { input, showResults };
};

// Registrierungsformular, wir übergeben den form name, die form attributsnamen, und den namen den die Form im Objekt bekommt
export const registerUserElements = getFormElements(
  "registration_form",
  "register-form-field",
  "registerForm"
);
// Login-Formulare, wir übergeben den form name, die form attributsnamen, und den namen den die Form im Objekt bekommt
export const loginUserElements = {
  ...getFormElements("login_form", "login-form-field", "loginForm"),
  ...additionalLoginElements(),
  ...getFormElements("passwordReset_form", "passwordReset-form-field", "passwordResetForm"),
};

// Password Reset Form
export const passwordResetElements = {
  ...getFormElements("password_reset_form", "changepassword-form-field", "changePasswordForm"),
};

export const editUserDataElements = {
  ...getFormElements("edit_user_form", "user-form-field", "editUserForm"),
  ...additionalEditUserElements(),
};

export const insertProductElements = {
  ...getFormElements("product_form", "product-form-field", "productForm"),
  ...additionalInsertProductElements(),
  ...additionalDeleteProductElements(),
  ...additionalEditProductElements(),
};

export const searchProductElements = {
  ...searchProductFormElements(),
};
