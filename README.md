### Repository Title: Fraud Detection API

### Description:
This repository contains the code for a Fraud Detection API developed in PHP. The API is designed to assess the risk associated with IP addresses by consolidating data from various sources and providing a risk assessment based on predefined criteria. It includes features such as IP validation, user behavior tracking, and external API integration for risk data retrieval.

### Key Features:
- IP validation and risk assessment
- User behavior tracking
- Integration with external APIs for risk data retrieval
- Secure authentication mechanism
- Detailed logging and monitoring capabilities

### Technologies Used:
- PHP
- JSON
- Curl
- Bootstrap
- jQuery

### Setup Instructions:
1. Clone the repository:
   ```sh
   git clone https://github.com/pra15mesh/ip-fraud-detection-api.git
   ```
2. Configure `config.php` with your API keys and settings.

### Folder Structure:
- `api.php`: Main API endpoint for risk assessment.
- `authmiddleware.php`: Authentication middleware for validating API requests.
- `logger.php`: Logging class for detailed logging of API requests.
- `utils.php`: Utility functions for making API requests and data processing.
- `index.php`: Admin panel for managing and monitoring the API.
- `config.php.env`: Add the API Keys which are available from https://db-ip.com/ and https://scamalytics.com/ (Rename config.php.env to config.php)

### Contributing:
1. Fork the repository.
2. Create a new branch (`git checkout -b feature/new-feature`).
3. Make your changes.
4. Commit your changes (`git commit -am 'Add new feature'`).
5. Push to the branch (`git push origin feature/new-feature`).
6. Create a new Pull Request.

### License:
This project is licensed under the MIT License - see the `LICENSE` file for details.

### Note:
This PHP-based Fraud Detection API was crafted with assistance from ChatGPT-4.
