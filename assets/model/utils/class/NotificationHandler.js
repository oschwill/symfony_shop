export class NotificationHandler {
  static showNotification(form, message, className, alertClass) {
    const container = this.getContainer(form, className, message);
    if (!container) return;
    this.clearContainer(container);

    // Neue Benachrichtigung erstellen und hinzufügen
    const notificationElement = document.createElement("div");
    notificationElement.className = `alert ${alertClass}`; // Bootstrap Alert Klassen
    notificationElement.textContent = message;

    // Benachrichtigung an den Container anhängen
    container.appendChild(notificationElement);
  }

  static clearContainer(container) {
    // Falls vorhanden, alles vorherige entfernen
    while (container.firstChild) {
      container.removeChild(container.firstChild);
    }
  }

  static getContainer(form, className, message) {
    const container = form.querySelector(className);

    // Wenn kein Container vorhanden ist, Fehlermeldung in der Konsole ausgeben
    if (!container) {
      console.error(`CRITICAL ERROR! ${message}`);
      return null;
    }

    return container;
  }
}
