<?php

namespace App\Enum;

enum ErrorMessage: string
{
    case OTP_DID_NOT_MATCH_ERROR = 'otp_not_match';
    case OTP_TIMEOUT_ERROR = 'otp_code_has_expired.';
    case OTP_DID_NOT_SENT_ERROR = 'otp_could_not_be_sent.';
    case OTP_SESSION_TOKEN_REQUIRED = 'otp_session_token_is_required.';
    case UNAUTHORIZED = 'unauthorized';
    case USER_NOT_FOUND = 'user_not_found';
    case CARDS_RETRIEVED = 'cards_retrieved';
    case REGISTRATION_FAILED = 'registration_failed';
    case PRE_LOGIN_FAILED = 'pre_login_failed';
    case LOGIN_FAILED = 'login_failed';
    case PHONE_NOT_FOUND = 'phone_not_found';
    case SERVER_ERROR = 'server_error';
    case ORDER_CREATION_FAILED = 'order_creation_failed';
    case CERTIFICATE_ORDER_CREATION_FAILED = 'certificate_order_creation_failed';
    case CONTACT_MESSAGE_FAILED = 'contact_message_failed';
    case LOAN_ORDER_CREATION_FAILED = 'loan_order_creation_failed';
    case CREDIT_REQUIRED = 'select_loan';
    case YEARS_REQUIRED = 'enter_the_loan_term.';
    case AMOUNT_REQUIRED = 'amount_required';
    case INTEREST_REQUIRED = 'interest_required';
    case PATENT_NUMBER_REQUIRED = 'patent_number_required';
    case REGISTRATION_NUMBER_REQUIRED = 'registration_number_required';
    case WORK_ADDRESS_REQUIRED = 'work_address_required';
    case WORKPLACE_REQUIRED = 'workplace_required';
    case POSITION_REQUIRED = 'position_required';
    case MANAGER_WORK_ADDRESS_REQUIRED = 'manager_work_address_required';
    case PHONE_NUMBER_REQUIRED = 'phone_number_required';
    case SALARY_REQUIRED = 'salary_required';
    case SALARY_MIN = 'salary_min';
    case COUNTRY_REQUIRED = 'country_required';
    case BANK_BRANCH_REQUIRED = 'bank_branch_required';
    case TERM_MIN = 'term_min';
    case AMOUNT_MIN = 'amount_min';
    case MONTHLY_PAYMENT_REQUIRED = 'monthly_payment_required';
    case ROLE_REQUIRED = 'role_required';
    case ROLE_INVALID = 'role_invalid';
    case CARD_TYPE_REQUIRED = 'card_type_required';
    case CARD_TYPE_INVALID = 'card_type_invalid';
    case PHONE_NUMBER_INVALID = 'phone_number_invalid';
    case BANK_BRANCH_INVALID = 'bank_branch_invalid';
    case INTERNET_SERVICE_REQUIRED = 'internet_service_required';
    case DELIVERY_REQUIRED = 'delivery_required';
    case EMAIL_REQUIRED = 'email_required';
    case EMAIL_INVALID = 'email_invalid';
    case WORK_POSITION_STRING = 'work_position_string';
    case WORK_PHONE_INTEGER = 'work_phone_integer';
    case USER_PROFILE_REQUIRED = 'user_profile_required';
    case CERTIFICATE_TYPE_REQUIRED = 'certificate_type_required';
    case CERTIFICATE_TYPE_INVALID = 'certificate_type_invalid';
    case HOME_ADDRESS_REQUIRED = 'home_address_required';
    case NAME_REQUIRED = 'name_required';
    case MESSAGE_REQUIRED = 'message_required';
    case OTP_REQUIRED = 'otp_required';
    case OTP_INVALID = 'otp_invalid';
    case PASSWORD_REQUIRED = 'password_required';
    case PASSWORD_MIN = 'password_min';
    case OTP_PURPOSE_REQUIRED = 'otp_purpose_required';
    case OTP_PURPOSE_INVALID = 'otp_purpose_invalid';
    case FIRST_NAME_REQUIRED = 'first_name_required';
    case LAST_NAME_REQUIRED = 'last_name_required';
    case BIRTH_DATE_REQUIRED = 'birth_date_required';
    case BIRTH_DATE_INVALID = 'birth_date_invalid';
    case PASSPORT_NUMBER_REQUIRED = 'passport_number_required';
    case GENDER_REQUIRED = 'gender_required';
    case GENDER_INVALID = 'gender_invalid';
    case ISSUED_DATE_REQUIRED = 'issued_date_required';
    case ISSUED_DATE_INVALID = 'issued_date_invalid';
    case ISSUED_BY_REQUIRED = 'issued_by_required';
    case SCAN_PASSPORT_REQUIRED = 'scan_passport_required';
    case SCAN_PASSPORT_INVALID = 'scan_passport_invalid';
    case CITIZENSHIP_REQUIRED = 'citizenship_required';
    case PHONE_REQUIRED = 'phone_required';
    case PHONE_INVALID = 'phone_invalid';
    case OTP_CODE_REQUIRED = 'otp_code_required';
    case OTP_CODE_INVALID = 'otp_code_invalid';
    case PHONE_ALREADY_REGISTERED = 'phone_already_registered';
    case PHONE_NOT_REGISTERED = 'phone_not_registered';
    case OTP_SESSION_INVALID = 'otp_session_invalid';
    case INCORRECT_PASSWORD = 'incorrect_password';
    case USER_PROFILE_ALREADY_EXISTS = 'user_profile_already_exists';
    case AMOUNT_EXCEEDS_LIMIT = 'requested_amount_exceeds_credit_limit';
    case TERM_EXCEEDS_LIMIT = 'requested_term_exceeds_credit_limit';
    case SCAN_PASSPORT_FILE = 'passport_scan_must_be_a_file.';
    case SCAN_PASSPORT_MIMES = 'passport_scan_must_be_a_JPG,_JPEG,_PNG,_or_PDF_file.';
    case HOME_PHONE_INTEGER = 'home_phone_must_be_an_integer.';

    // #2

    case DEPOSIT_TYPE_NOT_FOUND = 'deposit_type_not_found.';
    case TARIFF_TYPE_NOT_FOUND = 'tariff_type_not_found.';
    case AWARD_TYPE_NOT_FOUND = 'award_type_not_found.';
    case THIS_LOAN_CANNOT_BE_APPLIED_ONLINE = 'this_loan_cannot_be_applied_online';
    case INVALID_OR_EXPIRED_OTP ='invalid_or_expired_otp';
    case INVALID_SECRET_WORD_TYPE='secret_word_must_be_a_valid_string';
    case PAYMENT_TYPE_REQUIRED = 'payment_type_is_required';
    case PAYMENT_TYPE_INTEGER = 'payment_type_must_be_a_valid_id';
    case PAYMENT_TYPE_NOT_EXIST = 'selected_payment_type_does_not_exist';

    case BRANCH_REQUIRED = 'branch_is_required';
    case BRANCH_INTEGER = 'branch_must_be_a_valid_id';
    case BRANCH_NOT_EXIST = 'selected_branch_does_not_exist_or_is_not_a_valid_branch';

    case UPLOADED_FILES_REQUIRED = 'you_must_upload_at_least_one_file';
    case UPLOADED_FILES_ARRAY = 'uploaded_files_must_be_an_array';
    case UPLOADED_FILES_MIN = 'you_must_upload_at_least_one_file_min';

    case UPLOADED_FILE_ITEM = 'each_uploaded_item_must_be_a_valid_file';
    case UPLOADED_FILE_MAX = 'each_file_cannot_exceed_10mb_in_size';
    case UPLOADED_FILES_COUNT = 'exactly_{count}_files_must_be_uploaded';

}
