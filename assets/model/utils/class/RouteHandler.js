export class ApiRoutes {
  constructor() {
    this.apiUrl = process.env.API_BASE_URL;
  }

  get routes() {
    return {
      passwordResetRoute: `${this.apiUrl}/api/v1/password-reset`,
      createNewProductRoute: `${this.apiUrl}/api/v1/create-product`,
      updateProductRoute: (id) => `${this.apiUrl}/api/v1/update-product/${id}`,
      deleteProductRoute: `${this.apiUrl}/api/v1/delete-product`,
      searchProductRoute: `${this.apiUrl}/api/v1/elastic-search`,
    };
  }
}
