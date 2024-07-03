/*********************************************************************************
 * PARTIE CONSTANTES
 *********************************************************************************/

const CSS_SIMPLE_PROPERTIES = [
    ["--font-size-title", 2, "px", 10, 45], // fontSizeTitle{In-Decrease}Button
    ["--font-size-text", 2, "px", 10, 28],    // fontSizeText{In-Decrease}Button
    ["--line-height", 0.1, "", 0.85, 2.5],       // lineHeight{In-Decrease}Button
    ["--letter-spacing", 0.05, "em", -0.15, 0.7], // letterSpacing{In-Decrease}Button
    ["--word-spacing", 0.1, "em", 0, 1],    // wordSpacing{In-Decrease}Button
    ["--margin", 5, "px", 0, 45],            // margin{In-Decrease}Button
    ["--padding", 5, "px", 0, 35],           // padding{In-Decrease}Button
    ["--border-radius", 4, "px", 0, 50],     // borderRadius{In-Decrease}Button
];

const CSS_COMPLEXE_PROPERTIES = [
    ["--background-color", "backgroundColor", ["#F8F9FA", "#F5C3C2", "#A7D2E8", "#FEF0C3", "#C1E1C1", "#E6E6FA"]],
    ["--text-color", "color", ["#0c0c0c", "#720b0a", "#063c5a", "#695803", "#005602", "#4e4ef3"]],
    ["--font-family", "fontFamily", ["Arial", "Verdana", "Georgia", "Courier New", "Roboto", "Ms Gothic", "Garamond"]],
    ["--text-align", "textAlign", ["left", "right", "center", "justify"]],];

class Config {
    static accessibilityPanel = $("#accessibilityPanel");
    static accessibilityStorageName = "accessibility";
}


/*********************************************************************************
 * PARTIE OUTILS
 *********************************************************************************/

/**
 * Capitalise la première lettre d'une chaîne de caractères.
 *
 * @param {string} string - La chaîne de caractères à capitaliser.
 * @returns {string} La chaîne de caractères avec la première lettre capitalisée.
 */
function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

/**
 * Fonction utilitaire pour sélectionner un élément du DOM.
 * @param {string} selector - Le sélecteur CSS de l'élément à sélectionner.
 * @returns {Element} L'élément correspondant au sélecteur.
 */
function $(selector) {
    return document.querySelector(selector);
}

/*********************************************************************************
 * PARTIE ENREGISTREMENT
 *********************************************************************************/

/**
 * Enregistre les paramètres d'accessibilité dans le localStorage.
 * @param {string} cssCategory - La catégorie du paramètre (select, range, ou boolean).
 * @param {string} cssProperty - La propriété CSS à enregistrer.
 * @param {string|number|boolean} cssValue - La valeur de la propriété CSS à enregistrer.
 */
function saveAccessibilitySettings(cssCategory, cssProperty, cssValue) {
    const existingAccessibilitySettings = JSON.parse(localStorage.getItem(Config.accessibilityStorageName)) || {};
    // Assurez-vous que la catégorie existe
    if (!existingAccessibilitySettings[cssCategory]) {
        existingAccessibilitySettings[cssCategory] = {};
    }

    // Enregistrez la valeur dans la catégorie appropriée
    existingAccessibilitySettings[cssCategory][cssProperty] = cssValue;

    // Sauvegardez les paramètres mis à jour
    localStorage.setItem(Config.accessibilityStorageName, JSON.stringify(existingAccessibilitySettings));
}


/*********************************************************************************
 * PARTIE CLASSE
 *********************************************************************************/

class CssProperty {
    #propertyName;

    constructor(propertyName) {
        this.#propertyName = propertyName;
    }

    getPropertyName() {
        return this.#propertyName;
    }

    capitalizePrefix(words) {
        return words[0] + words
            .slice(1)
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join('');
    }
}

class CssSimpleProperty extends CssProperty {
    #value;
    #unit;
    #delayAnimation;
    #classAnimation;
    #decreaseButton;
    #increaseButton;
    #min;
    #max;

    /**
     * Constructeur de la classe CssNumberProperty.
     *
     * @param {string} propertyName - Le nom de la propriété CSS (ex: "my-custom-property").
     * @param {number} value - La valeur initiale de la propriété CSS.
     * @param {string} [unit=px] - L'unité de la propriété CSS (par défaut : "px").
     * @param {number} min - La valeur minimale.
     * @param {string} max - La valeur maximale.
     */
    constructor(propertyName, value, unit = "px", min, max) {
        super(propertyName);
        this.#value = value;
        this.#unit = unit;
        this.#min = min;
        this.#max = max;
        this.setCssPropertyButtons(propertyName);
        this.#delayAnimation = 300;
        this.#classAnimation = "animate";
        this.#initialize();
    }

    getValue() {
        return this.#value;
    }

    getUnit() {
        return this.#unit;
    }

    getDecreaseButton() {
        return this.#decreaseButton;
    }

    setDecreaseButton(value) {
        this.#decreaseButton = $(value);
    }

    getIncreaseButton() {
        return this.#increaseButton;
    }

    setIncreaseButton(value) {
        this.#increaseButton = $(value);
    }

    getDelayAnimation() {
        return this.#delayAnimation;
    }

    getClassAnimation() {
        return this.#classAnimation;
    }

    getMax() {
        return this.#max;
    }

    getMin() {
        return this.#min;
    }

    /**
     * Définit les boutons d'augmentation et de diminution pour une propriété CSS.
     *
     * @param {string} cssProperty - La propriété CSS complète (ex: "--my-custom-property-buttons").
     */
    setCssPropertyButtons(cssProperty) {
        const parts = cssProperty.split('-').slice(2);
        const buttonPrefix = this.capitalizePrefix(parts)

        this.setDecreaseButton(`#${buttonPrefix}DecreaseButton`);
        this.setIncreaseButton(`#${buttonPrefix}IncreaseButton`);
    }

    /**
     * Initialise l'ajustement de la propriété CSS.
     * Ajoute des écouteurs d'événement "click" aux boutons de diminution et d'augmentation de la valeur.
     */
    #initialize() {
        this.#addEventButton(this.getDecreaseButton(), -1 * this.getValue());
        this.#addEventButton(this.getIncreaseButton(), this.getValue());
    }

    /**
     * Ajoute un événement de clic à un élément et ajuste la racine du document en fonction de la valeur passée.
     *
     * @param {HTMLElement} element - L'élément auquel l'événement de clic doit être ajouté.
     * @param {any} value - La valeur à utiliser pour ajuster la racine du document.
     */
    #addEventButton(element, value) {
        element.addEventListener("click", () => {
            this.#adjustRoot(value);
            element.classList.add(this.getClassAnimation());
            setTimeout(() => {
                element.classList.remove(this.getClassAnimation());
            }, this.getDelayAnimation());
        });
    }

    /**
     * Ajuste la valeur d'une propriété CSS sur l'élément racine (root) et enregistre les
     * paramètres d'accessibilitédans le localStorage.
     * @param {number} adjustmentValue - La valeur d'ajustement à ajouter à la propriété CSS.
     */
    #adjustRoot(adjustmentValue) {
        const rootElement = document.documentElement;
        const currentRootValue = parseFloat(getComputedStyle(rootElement)
            .getPropertyValue(this.getPropertyName()));
        let newPropertyValue = currentRootValue + adjustmentValue;

        // Paramétrages
        if (newPropertyValue >= this.getMax()) newPropertyValue = this.getMax();
        if (newPropertyValue <= this.getMin()) newPropertyValue = this.getMin();

        const newPropertyValueWithUnit = `${newPropertyValue}${this.getUnit()}`;
        rootElement.style.setProperty(this.getPropertyName(), newPropertyValueWithUnit);
        saveAccessibilitySettings("range", this.getPropertyName(), newPropertyValueWithUnit);
    }
}

class CssSelectProperty extends CssProperty {
    #propertyStyle;
    #valuesArray;
    #propertySelect;

    /**
     * Constructeur de la classe CssSelectProperty.
     *
     * @param {string} propertyName - Le nom de la propriété CSS.
     * @param {string} propertyStyle - Le style de la propriété CSS.
     * @param {string[]} valueArray - Le tableau de valeurs de la propriété CSS.
     */
    constructor(propertyName, propertyStyle, valueArray,) {
        super(propertyName)
        this.#propertyStyle = propertyStyle;
        this.#valuesArray = valueArray;
        this.setCssPropertySelect(propertyName)
    }

    getPropertySelect() {
        return this.#propertySelect;
    }

    setPropertySelect(value) {
        this.#propertySelect = $(value);
    }

    getValuesArray() {
        return this.#valuesArray;
    }

    getPropertyStyle() {
        return this.#propertyStyle;
    }

    /**
     * Définit la propriété CSS sélectionnée.
     *
     * @param {string} cssProperty - La propriété CSS complète.
     */
    setCssPropertySelect(cssProperty) {
        const parts = cssProperty.split('-').slice(2);
        const selectPrefix = this.capitalizePrefix(parts);
        this.setPropertySelect(`#${selectPrefix}Select`);
    }

    /**
     * Initialise l'événement 'change' sur le select de la famille de polices.
     * Lorsque l'utilisateur sélectionne une nouvelle famille de polices :
     * - La valeur du select est appliquée à sa propre police de caractères pour un aperçu en temps réel
     * - La propriété CSS "--font-family" est mise à jour avec la nouvelle valeur
     * - Les paramètres d'accessibilité sont enregistrés dans le localStorage avec la nouvelle famille de polices
     */
    initialize() {
        this.getPropertySelect().addEventListener("change", () => {
            const selectedProperty = this.getPropertySelect().value;
            this.getPropertySelect().style[this.getPropertyStyle()] = this.getPropertySelect().value;
            document.documentElement.style.setProperty(this.getPropertyName(), selectedProperty);
            saveAccessibilitySettings("select", this.getPropertyName(), selectedProperty);
        });
    }

    /**
     * Crée les options du select de la famille de polices et sélectionne l'option correspondant
     * à la valeur actuelle.
     */
    createPropertyOptions(settings) {
        const currentValue = settings[this.getPropertyName()] || this.getValuesArray()[0];

        // Supprimer les options existantes
        this.getPropertySelect().innerHTML = "";

        // Créer les options pour chaque famille de polices
        this.getValuesArray().forEach(value => {
            const option = document.createElement("option");
            option.value = value;
            option.text = value;

            if (value === currentValue) {
                option.selected = true;
            }
            option.style[this.getPropertyStyle()] = value;
            this.getPropertySelect().add(option);
        });

        this.getPropertySelect().style[this.getPropertyStyle()] = currentValue;
    }
}

class CssReadGuideProperty {
    #height;
    #backgroundColor;
    #opacity;
    #readGuideBottom;
    #readGuideTop;

    /**
     * Crée une nouvelle instance de CssReadGuideProperty.
     *
     * @param {number} height - La hauteur du guide de lecture.
     * @param {string} backgroundColor - La couleur de fond du guide de lecture au format hexadécimal (ex: "#FFFFFF").
     * @param {number} opacity - L'opacité du guide de lecture (valeur entre 0 et 1).
     */
    constructor(height, backgroundColor, opacity) {
        this.#height = height;
        this.#opacity = opacity;
        this.#backgroundColor = this.createBackgroundColor(backgroundColor);
        this.#readGuideBottom = this.createReadGuide("bottom");
        this.#readGuideTop = this.createReadGuide("top");
        this.addEventMouse();
    }

    getReadGuideBottom() {
        return this.#readGuideBottom;
    }

    getReadGuideTop() {
        return this.#readGuideTop;
    }

    getHeight() {
        return this.#height;
    }

    getBackgroundColor() {
        return this.#backgroundColor;
    }

    getOpacity() {
        return this.#opacity;
    }

    /**
     * Crée la couleur de fond du guide de lecture au format RGBA.
     *
     * @param {string} backgroundColor - La couleur de fond au format hexadécimal (ex: "#FFFFFF").
     * @returns {string} La couleur de fond au format RGBA.
     */
    createBackgroundColor(backgroundColor) {
        const r = parseInt(backgroundColor.slice(1, 3), 16);
        const g = parseInt(backgroundColor.slice(3, 5), 16);
        const b = parseInt(backgroundColor.slice(5, 7), 16);
        const hetToRGB = `${r}, ${g}, ${b}`;
        return `rgba(${hetToRGB}, ${this.getOpacity()})`;
    }

    /**
     * Crée un élément de guide de lecture.
     *
     * @param {string} type - Le type de guide de lecture ("top" ou "bottom").
     * @returns {HTMLElement} L'élément de guide de lecture créé.
     */
    createReadGuide(type) {
        let guideElement = document.createElement("div");
        guideElement.id = "readGuide" + capitalizeFirstLetter(type);
        guideElement.style.position = "fixed";
        guideElement.style.width = "100%";
        guideElement.style.height = "100%";
        guideElement.style.left = "0";
        guideElement.style.backgroundColor = this.getBackgroundColor();
        guideElement.style.display = "block";
        guideElement.style.zIndex = "9999";
        $("#readGuideContainer").appendChild(guideElement);
        return guideElement;
    }

    /**
     * Ajoute un événement de clic sur le bouton "readGuideButton" pour afficher/masquer les guides de lecture.
     */
    addEventMouse() {
        document.addEventListener("mousemove", (event) => {
            const middleHeight = this.getHeight() / 2;
            this.getReadGuideBottom().style.top = `${event.clientY + this.getHeight() / 2}px`;
            this.getReadGuideTop().style.bottom = `${window.innerHeight - event.clientY + this.getHeight() / 2}px`;
        });
    }

    addEventListeners() {
        $("#readGuideButton").addEventListener("click", () => {
            const currentValue = document.documentElement.style.getPropertyValue("--read-guide-display");
            const newValue = currentValue === "block" ? "none" : "block";
            document.documentElement.style.setProperty("--read-guide-display", newValue);
            saveAccessibilitySettings("boolean", "--read-guide-display", newValue);
        });
    }
}


/*********************************************************************************
 * PARTIE INITIALISATION
 *********************************************************************************/

/**
 * Crée des objets CssSelectProperty à partir d'une liste de propriétés.
 *
 * @param {Array} properties - Liste de propriétés à créer. Chaque propriété est un tableau contenant trois éléments :
 *   - Le premier élément est le nom de la propriété.
 *   - Le deuxième élément est la valeur par défaut de la propriété.
 *   - Le troisième élément est la description de la propriété.
 */
function createCssSelectProperties(properties) {
    properties.forEach(property => {
        const selectProperty = new CssSelectProperty(property[0], property[1], property[2]);
        selectProperty.initialize();
        selectProperty.createPropertyOptions(property[1],);
    });
}

/**
 * Crée des objets CssSimpleProperty à partir d'une liste de propriétés.
 *
 * @param {Array} properties - Liste de propriétés à créer. Chaque propriété est un tableau contenant trois éléments :
 *   - Le premier élément est le nom de la propriété.
 *   - Le deuxième élément est la valeur par défaut de la propriété.
 *   - Le troisième élément est la description de la propriété.
 */
function createCssSimpleProperties(properties) {
    properties.forEach(property => {
        new CssSimpleProperty(property[0], property[1], property[2], property[3], property[4]);
    });
}


/**
 * Lit les paramètres d'accessibilité depuis le localStorage et met à jour les styles en conséquence.
 * Si le panel de réglages de la famille de polices existe, il crée les options du select avec
 * la valeur actuelle sélectionnée.
 */
function readAccessibilitySettings() {
    const accessibilitySettings = JSON.parse(localStorage.getItem(Config.accessibilityStorageName)) || {};

    // Mettre à jour les variables CSS avec les valeurs enregistrées
    for (const category in accessibilitySettings) {
        for (const [property, value] of Object.entries(accessibilitySettings[category])) {
            document.documentElement.style.setProperty(property, value);
        }
    }

    const read = new CssReadGuideProperty(100, "#000000", 0.6);

    if (!Config.accessibilityPanel) return;

    // Créer le comportement des propriétés css simple à valeur
    createCssSimpleProperties(CSS_SIMPLE_PROPERTIES);

    // Créer le comportement des selects
    createCssSelectProperties(CSS_COMPLEXE_PROPERTIES);

    // Créer le comportement du reset
    $("#resetButton").addEventListener("click", () => {
        localStorage.removeItem(Config.accessibilityStorageName);
        location.reload();
    });

    read.addEventListeners();
}

readAccessibilitySettings();
