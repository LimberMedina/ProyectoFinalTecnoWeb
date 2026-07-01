import { ref, computed } from "vue";

export function useRegisterValidation() {
    const errors = ref({
        nombre: "",
        apellidos: "",
        ci: "",
        telefono: "",
        email: "",
        fecha_nacimiento: "",
        password: "",
        password_confirmation: "",
        role_id: "",
    });

    // Estados para validación de email
    const isVerifyingEmail = ref(false);
    const emailVerificationStatus = ref(null); // 'valid', 'invalid', null
    let emailVerificationTimeout = null;

    // Debounce para validación de email
    const debounce = (func, wait) => {
        return (...args) => {
            clearTimeout(emailVerificationTimeout);
            emailVerificationTimeout = setTimeout(() => func(...args), wait);
        };
    };

    // Validar email localmente sin API
    const verifyEmailWithAPI = async (email) => {
        if (!email) {
            emailVerificationStatus.value = null;
            return;
        }

        // Validar formato
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            emailVerificationStatus.value = null;
            return;
        }

        // Lista de dominios desechables comunes
        const disposableDomains = [
            "tempmail.com",
            "guerrillamail.com",
            "10minutemail.com",
            "mailinator.com",
            "throwaway.email",
            "temp-mail.org",
            "yopmail.com",
            "maildrop.cc",
            "sharklasers.com",
            "spam4.me",
        ];

        const domain = email.split("@")[1].toLowerCase();

        // Simular verificación (validar que no sea desechable)
        isVerifyingEmail.value = true;
        emailVerificationStatus.value = null;

        // Simular delay de verificación
        await new Promise((resolve) => setTimeout(resolve, 800));

        if (disposableDomains.includes(domain)) {
            emailVerificationStatus.value = "invalid";
            errors.value.email = "No se permiten correos desechables.";
        } else {
            emailVerificationStatus.value = "valid";
            errors.value.email = "";
        }

        isVerifyingEmail.value = false;
    };

    // Debounced version of email verification
    const verifyEmailDebounced = debounce(verifyEmailWithAPI, 500);

    // Validadores
    const validateNombre = (value) => {
        if (!value) return "";
        if (!/^[a-záéíóúñ\s]+$/i.test(value)) {
            return "El nombre solo puede contener letras y espacios.";
        }
        const words = value.trim().split(/\s+/);
        if (words.length > 3) {
            return "El nombre no puede tener más de 3 palabras.";
        }
        if (value.length < 2) {
            return "El nombre debe tener al menos 2 caracteres.";
        }
        if (value.length > 50) {
            return "El nombre no puede exceder 50 caracteres.";
        }
        return "";
    };

    const validateApellidos = (value) => {
        if (!value) return "";
        if (!/^[a-záéíóúñ\s]+$/i.test(value)) {
            return "Los apellidos solo pueden contener letras y espacios.";
        }
        const words = value.trim().split(/\s+/);
        if (words.length > 3) {
            return "Los apellidos no pueden tener más de 3 palabras.";
        }
        if (value.length < 2) {
            return "Los apellidos deben tener al menos 2 caracteres.";
        }
        if (value.length > 100) {
            return "Los apellidos no pueden exceder 100 caracteres.";
        }
        return "";
    };

    const validateCI = (value) => {
        if (!value) return "";
        if (!/^[\d\-\.]+$/.test(value)) {
            return "El CI/RUT solo puede contener números, guiones y puntos.";
        }
        if (value.length < 5) {
            return "El CI/RUT debe tener al menos 5 caracteres.";
        }
        if (value.length > 20) {
            return "El CI/RUT no puede exceder 20 caracteres.";
        }
        return "";
    };

    const validateTelefono = (value) => {
        if (!value) return "";
        if (!/^[\d\s\(\)\+\-\.]+$/.test(value)) {
            return "El teléfono solo puede contener números, espacios y símbolos +().-";
        }
        if (value.replace(/\D/g, "").length < 7) {
            return "El teléfono debe tener al menos 7 dígitos.";
        }
        if (value.length > 20) {
            return "El teléfono no puede exceder 20 caracteres.";
        }
        return "";
    };

    const validateEmail = (value) => {
        if (!value) return "";
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            return "El correo electrónico no es válido.";
        }
        return "";
    };

    const validatePassword = (value) => {
        if (!value) return "";
        if (value.length < 8) {
            return "La contraseña debe tener al menos 8 caracteres.";
        }
        if (!/[A-Z]/.test(value)) {
            return "La contraseña debe contener al menos una letra mayúscula.";
        }
        if (!/[a-z]/.test(value)) {
            return "La contraseña debe contener al menos una letra minúscula.";
        }
        if (!/[0-9]/.test(value)) {
            return "La contraseña debe contener al menos un número.";
        }
        return "";
    };

    const validatePasswordConfirmation = (password, confirmation) => {
        if (!confirmation) return "";
        if (password !== confirmation) {
            return "Las contraseñas no coinciden.";
        }
        return "";
    };

    const validateFechaNacimiento = (value) => {
        if (!value) return "";
        const date = new Date(value);
        const today = new Date();
        let age = today.getFullYear() - date.getFullYear();
        const monthDiff = today.getMonth() - date.getMonth();
        if (
            monthDiff < 0 ||
            (monthDiff === 0 && today.getDate() < date.getDate())
        ) {
            age--;
        }
        if (age < 13) {
            return "Debes tener al menos 13 años para registrarte.";
        }
        if (age > 150) {
            return "La fecha de nacimiento no es válida.";
        }
        return "";
    };

    const validateRoleId = (value) => {
        if (!value) return "Debe seleccionar un rol.";
        return "";
    };

    // Actualizar error
    const updateError = (field, value, otherValue = null) => {
        switch (field) {
            case "nombre":
                errors.value.nombre = validateNombre(value);
                break;
            case "apellidos":
                errors.value.apellidos = validateApellidos(value);
                break;
            case "ci":
                errors.value.ci = validateCI(value);
                break;
            case "telefono":
                errors.value.telefono = validateTelefono(value);
                break;
            case "email":
                // Validación local primero
                const localEmailError = validateEmail(value);
                if (localEmailError) {
                    errors.value.email = localEmailError;
                    emailVerificationStatus.value = null;
                } else {
                    // Si el formato es válido, verificar con API
                    verifyEmailDebounced(value);
                }
                break;
            case "fecha_nacimiento":
                errors.value.fecha_nacimiento = validateFechaNacimiento(value);
                break;
            case "password":
                errors.value.password = validatePassword(value);
                // Revalidar confirmación si existe
                if (otherValue !== null) {
                    errors.value.password_confirmation =
                        validatePasswordConfirmation(value, otherValue);
                }
                break;
            case "password_confirmation":
                errors.value.password_confirmation =
                    validatePasswordConfirmation(otherValue, value);
                break;
            case "role_id":
                errors.value.role_id = validateRoleId(value);
                break;
        }
    };

    // Validar todo el formulario
    const validateAll = (formData) => {
        errors.value.nombre = validateNombre(formData.nombre);
        errors.value.apellidos = validateApellidos(formData.apellidos);
        errors.value.ci = validateCI(formData.ci);
        errors.value.telefono = validateTelefono(formData.telefono);
        errors.value.email = validateEmail(formData.email);
        errors.value.fecha_nacimiento = validateFechaNacimiento(
            formData.fecha_nacimiento,
        );
        errors.value.role_id = validateRoleId(formData.role_id);
        errors.value.password = validatePassword(formData.password);
        errors.value.password_confirmation = validatePasswordConfirmation(
            formData.password,
            formData.password_confirmation,
        );

        return !Object.values(errors.value).some((e) => e !== "");
    };

    const hasErrors = computed(() =>
        Object.values(errors.value).some((e) => e !== ""),
    );

    const clearErrors = () => {
        Object.keys(errors.value).forEach((key) => {
            errors.value[key] = "";
        });
        emailVerificationStatus.value = null;
        isVerifyingEmail.value = false;
    };

    return {
        errors: computed(() => errors.value),
        updateError,
        validateAll,
        hasErrors,
        clearErrors,
        isVerifyingEmail: computed(() => isVerifyingEmail.value),
        emailVerificationStatus: computed(() => emailVerificationStatus.value),
    };
}
