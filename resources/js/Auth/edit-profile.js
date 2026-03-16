document.addEventListener("DOMContentLoaded", initializeProfileManagement);

async function initializeProfileManagement() {
    const apiToken = localStorage.getItem("api_token");

    if (!apiToken) {
        executeLogout();
        return;
    }

    configureAxiosAuthorization(apiToken);
    await fetchAndRenderUserProfile();
    initializeAvatarUploadListener();
    initializeProfileFormListener();
}

function executeLogout() {
    localStorage.removeItem("api_token");
    window.location.href = "/login";
}

function configureAxiosAuthorization(token) {
    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
}

async function fetchAndRenderUserProfile() {
    try {
        const response = await axios.get("/api/user");
        const userData = response.data.data ?? response.data;
        populateProfileFields(userData);
    } catch (error) {
        await handleProfileFetchError(error);
    }
}

async function handleProfileFetchError(error) {
    const statusCode = error.response?.status;

    if (statusCode === 404) {
        await executeProfileFetchFallback();
        return;
    }

    if (statusCode === 401 || statusCode === 403) {
        executeLogout();
        return;
    }

    const errorMessage =
        error.response?.data?.message ||
        `Failed to fetch profile data (status: ${statusCode || "unknown"})`;
    showErrorAlert(errorMessage);
}

async function executeProfileFetchFallback() {
    try {
        const response = await axios.get("/api/me");
        const userData = response.data.data ?? response.data;
        populateProfileFields(userData);
    } catch (error) {
        const statusCode = error.response?.status;

        if (statusCode === 401 || statusCode === 403) {
            executeLogout();
            return;
        }

        const errorMessage =
            error.response?.data?.message ||
            "Failed to fetch profile data (fallback)";
        showErrorAlert(errorMessage);
    }
}

function populateProfileFields(user) {
    setElementValue("first_name", user.first_name);
    setElementValue("last_name", user.last_name);
    setElementValue("email", user.email);
    setElementValue("username", user.username);
    setElementImageSource("avatarPreview", user.avatar);
}

function setElementValue(elementId, value) {
    const element = document.getElementById(elementId);
    if (element && value !== undefined) {
        element.value = value;
    }
}

function setElementImageSource(elementId, srcUrl) {
    const element = document.getElementById(elementId);
    if (element && srcUrl) {
        element.src = srcUrl;
    }
}

function initializeAvatarUploadListener() {
    const avatarInput = document.getElementById("avatarInput");
    if (avatarInput) {
        avatarInput.addEventListener("change", handleAvatarPreview);
    }
}

function handleAvatarPreview(event) {
    const [selectedFile] = event.target.files;
    if (selectedFile) {
        const objectUrl = URL.createObjectURL(selectedFile);
        setElementImageSource("avatarPreview", objectUrl);
    }
}

function initializeProfileFormListener() {
    const profileForm = document.getElementById("formEditProfile");
    if (profileForm) {
        profileForm.addEventListener("submit", processProfileUpdate);
    }
}

async function processProfileUpdate(event) {
    event.preventDefault();
    const formElement = event.target;

    if (!isPasswordChangeValid(formElement)) {
        return;
    }

    await submitProfileFormData(formElement);
}

function isPasswordChangeValid(formElement) {
    const newPassword = formElement.querySelector(
        'input[name="password"]',
    )?.value;
    const currentPassword = formElement.querySelector(
        'input[name="current_password"]',
    )?.value;

    if (newPassword && !currentPassword) {
        Swal.fire(
            "Validation Error",
            "Current password is required to change password",
            "error",
        );
        return false;
    }

    return true;
}

async function submitProfileFormData(formElement) {
    const formData = new FormData(formElement);

    try {
        await axios.post("/api/profile/update", formData);
        await Swal.fire("Success", "Profile has been updated", "success");
        window.location.reload();
    } catch (error) {
        const errorMessage =
            error.response?.data?.message || "An error occurred";
        Swal.fire("Failed", errorMessage, "error");
    }
}

function showErrorAlert(message) {
    Swal.fire("Error", message, "error");
}
