import "animate.css";

import {
  clearSearchResults,
  showErrorModal,
  showSearchResults,
  showSuccessModal,
} from "../../view/partialUI";
import { elasticSearchRequest, manipulateProduct } from "../api/fetchData";
import { createNewUrlInputField } from "../interactions/generalInteractions";
import { limitPriceField, validatePictureUrls } from "../interactions/normalizeElements";
import { ApiRoutes } from "../utils/class/RouteHandler";
import { animateElement, getUrlId, isLastParamNumeric } from "../utils/helper/helperFunctions";

export const validateProductForm = (elements, event) => {
  // Hier bauen wir den price Validation mit ein
  if (event.target === elements.price) {
    limitPriceField(event);
  }
  // Müssen die pictures immer wieder neu holen falls sich die length geändert hat
  const pictureInputs = document.querySelectorAll("#product_pictures input[type='text']");

  validatePictureUrls(elements, pictureInputs);
};

export const createProduct = async (elements, event) => {
  const hasId = isLastParamNumeric();
  try {
    const pictureArray = [];

    // Alle Input-Felder für Bilder durchlaufen
    const formFields = Array.from(elements.productForm.elements);

    formFields.forEach((field) => {
      // Nur die Bilder holen
      if (
        field.id.startsWith("product_form_productPictures_") &&
        field.id.endsWith("_picturePath")
      ) {
        // ISt gültig? Sollte eigentlich
        if (field.classList.contains("is-valid")) {
          pictureArray.push(field.value);
          // manipulateProduct;
        }
      }
    });

    const productData = {
      title: elements.title.value,
      price: elements.price.value,
      description: elements.description.value,
      pictures: pictureArray,
    };
    // Daten übergeben an den Fetch
    const apiRoutes = new ApiRoutes();
    // Handelt es sichum ein neues oder um ein editierbares Produkt?=
    const fetchUrl = hasId
      ? apiRoutes.routes.updateProductRoute(getUrlId())
      : apiRoutes.routes.createNewProductRoute;

    const response = await manipulateProduct(productData, fetchUrl);

    const successType = hasId ? "update_product_success" : "create_product_success";

    // Abfragen ib create oder edit
    response.id ? showSuccessModal(successType, response.id) : showErrorModal();
  } catch (error) {
    console.error(error);
    showErrorModal(hasId ? "update_product_error" : "create_product_error");
  }
};

export const killProduct = async (elements) => {
  try {
    const apiRoutes = new ApiRoutes();
    const response = await manipulateProduct(
      elements.productId.value,
      apiRoutes.routes.deleteProductRoute,
      "Fehler beim Löschen des Produkts:"
    );

    if (response.status) {
      showSuccessModal("delete_product_success");
    }
  } catch (error) {
    console.error(error);
    showErrorModal("delete_product_error");
  }
};

export const searchProduct = async (elements) => {
  try {
    if (elements.input.value !== "") {
      const apiRoutes = new ApiRoutes();
      const response = await elasticSearchRequest(
        elements.input.value,
        apiRoutes.routes.searchProductRoute,
        "Fehler beim fetch der Elastic Daten:"
      );

      showSearchResults(response, elements);
      return;
    }

    // Sonst clearn
    clearSearchResults(elements);
  } catch (error) {
    console.warn(error);

    clearSearchResults(elements);
  }
};

export const toggleEditProductForm = (elements) => {
  const formContainer = elements.formContainer;

  if (formContainer.style.right === "0px" || formContainer.style.right === "") {
    // Schließe die Form
    formContainer.style.right = "-40%";
    formContainer.style.opacity = "0";
  } else {
    // Zeige die Form
    formContainer.style.right = "0";
    formContainer.style.opacity = "1";
  }
};

export const deleteProductPicture = (elements, event) => {
  const pictureContainer = event.target.closest("div.mb-2"); // parent holn

  if (pictureContainer) {
    pictureContainer.classList.add("animate__animated", "animate__slideOutRight");
    pictureContainer.style.animationDuration = "0.3s";

    pictureContainer.addEventListener(
      "animationend",
      () => {
        pictureContainer.remove();
        // Form neu validieren
        validateProductForm(elements, event);
      },
      { once: true }
    );
  }
};

export const resetEditProduct = (elements, event) => {
  const { originalValues, productPicturesContainer } = elements;

  if (productPicturesContainer) {
    // Resetten die Felder
    if (elements.title) {
      elements.title.value = originalValues.title;
    }

    if (elements.price) {
      elements.price.value = originalValues.price;
    }

    if (elements.description) {
      elements.description.value = originalValues.description;
    }

    if (productPicturesContainer) {
      productPicturesContainer.innerHTML = "";
      originalValues.pictures.forEach((picture, index) => {
        createNewUrlInputField(elements); // Erstelle ein neues Input-Feld

        // Hole den aktuellen Input nach dem Hinzufügen des neuen Felds
        const pictureInputs = productPicturesContainer.querySelectorAll(
          '[name^="product_form[productPictures]"]'
        );
        const currentInput = pictureInputs[index];
        if (currentInput) {
          currentInput.value = picture;

          // Aktualisieren des Bildes
          const imagePreview = currentInput.nextElementSibling;
          if (imagePreview && imagePreview.tagName === "IMG") {
            imagePreview.src = picture;
          }
        }
      });
    }

    // Animieren
    animateElement(productPicturesContainer, "animate__fadeInRight");

    validateProductForm(elements, event);
  }
};
