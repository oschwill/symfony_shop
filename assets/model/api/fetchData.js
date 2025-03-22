import axios from "axios";
import { ErrorHandler } from "../utils/class/ErrorHandler";
import { SuccessHandler } from "../utils/class/SuccessHandler";
import {
  hideLoadingSpinner,
  isValidEmail,
  setCountDown,
  showLoadingSpinner,
} from "../utils/helper/helperFunctions";
import { loadingSpinner } from "../../view/partialUI";

const apiUrl = process.env.API_BASE_URL;
const passwordResetRoute = `${apiUrl}/api/v1/password-reset`;

export const sendForgotPasswordRequest = async (userData) => {
  let emailInput = userData.emailForPasswordReset.value;

  if (!emailInput) {
    ErrorHandler.showError(
      userData.passwordResetForm,
      "Bitte füllen sie das Email Pflichtfeld aus.",
      ".form-message-container"
    );
    return;
  }

  if (!isValidEmail(emailInput)) {
    ErrorHandler.showError(
      userData.passwordResetForm,
      "Bitte geben Sie eine gültige E-Mail Adresse ein.",
      ".form-message-container"
    );

    return;
  }

  try {
    // Sobald der API Call stattfindet deaktivieren wir die Felder und setzen einen Countdown
    setCountDown(userData);

    // FETCHEN NUN DE DATA
    const response = await axios.post(
      passwordResetRoute,
      { email: emailInput },
      {
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      }
    );

    const data = response.data;

    if (data.error) {
      ErrorHandler.showError(
        userData.passwordResetForm,
        `Fehler: ${data.error}`,
        ".form-message-container"
      );
    } else {
      SuccessHandler.showSuccess(
        userData.passwordResetForm,
        `${data.message}`,
        ".form-message-container"
      );
    }
  } catch (error) {
    ErrorHandler.showError(
      userData.passwordResetForm,
      `Fehler: ${error.response.data.error}`,
      ".form-message-container"
    );
  }
};

export const manipulateProduct = async (data, url, message) => {
  showLoadingSpinner(loadingSpinner);
  try {
    const response = await axios.post(
      url,
      { data: data },
      {
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      }
    );

    hideLoadingSpinner();

    return response.data;
  } catch (error) {
    hideLoadingSpinner();
    console.warn(`${message}: `, error.message);

    throw error;
  }
};

export const elasticSearchRequest = async (data, url, message) => {
  try {
    const response = await axios.post(
      url,
      { data: data },
      {
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      }
    );

    return response.data;
  } catch (error) {
    console.warn(`${message}: `, error.message);

    throw error;
  }
};
