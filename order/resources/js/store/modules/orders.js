import ApiService from '@/utils/api';
import RecType from '@/JSON/index'
import Helpers from '@/utils/helpers.js';
import Language from '@/utils/language.js'
import store from '@/store';

export default {
    state () {
        return {
            inTesting: false,

            isLoading: false,
            isEditing: false,
            isDraft: false,
            editingOrder: null,
            stateCopy: null,
            emptyOrderCopy: {
                basicData: null,
                consumerInfo: null,
                formAndGeometry: null,
                form: null,
                frame: null,
                refraction: null,
            },
            erp: {
                defaultIniMode: 'FFUBAS',
                //getIni: null,
                getLensTypesOnly: null,
                getLensFilter: null,
                getRangesCombination: null,
                getSalesFramesSupplierList: null,
                getFrameByID: null,
                interpolate: null,
                scaleShape: null,
                getCalculation: null,
                getStockLensesByProdList: null,
                getStockItems: null,
                right: {
                    diamlist: null,
                    getLensGeo: null,
                },
                left: {
                    diamlist: null,
                    getLensGeo: null,
                },
            },
            notification: {
                message: '',
                durationSeconds: 0,
                type: '',
            },
            dialog: {
                hasDialog: false,
                type: '',
                title: '',
                description: '',
                leftButtonDescription: '',
                rightButtonDescription: '',
                allowClosing: false,
                extraButtons: [],
                wasOrderSavedFromFooter: false,
            },
            dashboardTitle: {
                get title() {return ""},
                get subTitle() {return ""}
            },
            basicData: {
                orderTypeSelection: [
                    {
                        id: 'N',
                        get title() {return store.getters.translations.newOrder.basicData.orderType.orderTypes.standardLens},
                        get show() {return store.getters.getIniProperties['SETTINGS_SHOW_LENS_ORDER']}
                    },
                    {
                        id: 'E',
                        get title() {return store.getters.translations.newOrder.basicData.orderType.orderTypes.remoteEdging},
                        get show() {return store.getters.getIniProperties['SETTINGS_SHOW_REMOTE_EDGING']}
                    },
                    {
                        id: 'G',
                        get title() {return store.getters.translations.newOrder.basicData.orderType.orderTypes.frameToCome},
                        get show() {return store.getters.getIniProperties['SETTINGS_SHOW_FRAME_TO_COME']}
                    },
                    {
                        id: 'S',
                        get title() {return store.getters.translations.newOrder.basicData.orderType.orderTypes.spectacles},
                        get show() {return store.getters.getIniProperties['SETTINGS_SHOW_COMPLETE_FRAME']}
                    },
                    {
                        id: 'W',
                        get title() {return store.getters.translations.newOrder.basicData.orderType.orderTypes.bulkOrder},
                        get show() {return store.getters.getIniProperties['SETTINGS_SHOW_BULK_ORDER']}
                    },
                ],
                selectedOrderType: null,
                referenceNumber: null,
                entryUser: null,
                custNumbX: null,
                remark: null,
                hasContinuedToDetails: false,
                additionalRemarkSelection: null,
                selectedAdditionalRemark: null
            },
            consumerInfo: {
                isExpanded: false,
                titleSelection: ['Mister', 'Mrs'],
                selectedTitle: null,
                name: null,
                firstName: null,
                street: null,
                zipCode: null,
                city: null,
                area: null,
                country: null,
                languageSelection: ['English', 'German', 'French', 'Italian'],
                selectedLanguage: null,
                phone: null,
                mobile: null,
                email: null,
                fax: null,
                dateOfBirth: null,
                res1: null,
                res2: null,
                res3: null,
                res4: null,
                res5: null,
                res6: null,
                res7: null,
                res8: null,
                res9: null,
                res10: null,
            },
            calculation: {
                isCalculationOpen: false,
                isLoading: false,
                areGraphicsSuccessfullyRendered: false,
                right: {
                    lensType: null,
                    diameter: null,
                    isOptimized: false,
                    geometrical: null,
                    optical: null,
                    minimal: null,
                    maximal: null,
                    top: null,
                    down: null,
                    nasal: null,
                    temporal: null,
                    basecurve: null,
                    weight: null,
                    error: null,
                    warning: null
                },
                left: {
                    lensType: null,
                    diameter: null,
                    isOptimized: false,
                    geometrical: null,
                    optical: null,
                    minimal: null,
                    maximal: null,
                    top: null,
                    down: null,
                    nasal: null,
                    temporal: null,
                    basecurve: null,
                    weight: null,
                    error: null,
                    warning: null
                }
            },
            details: {
                // Set sequence order for loading components in Details.vue and Sidebar.vue (lower value is first).
                // Each component can have it's own unique set of sequences for each ordertype that will override the default sequence.
                // component property is used for initializing the component in app.js.
                components: [
                    {
                        sequence: 10,
                        show: true,
                        enabled: true,
                        name: 'product-category',
                        get title() { return store.getters.translations.newOrder.details.productCategory.title},
                        component: require('@/views/dashboard/new_order/details/ProductCategory.vue'),
                        orderTypeSequence: [],
                    },
                    {
                        sequence: 11,
                        show: true,
                        enabled: true,
                        name: 'product',
                        get title() { return store.getters.translations.newOrder.details.product.title},
                        component: require('@/views/dashboard/new_order/details/Product.vue'),
                        orderTypeSequence: [],
                    },
                    {
                        sequence: 12,
                        show: true,
                        enabled: true,
                        name: 'refraction',
                        get title() { return store.getters.translations.newOrder.details.refraction.title},
                        component: require('@/views/dashboard/new_order/details/Refraction.vue'),
                        orderTypeSequence: [],
                    },
                    {
                        sequence: 13,
                        show: false,
                        enabled: true,
                        name: 'individual',
                        get title() { return store.getters.translations.newOrder.details.individual.title},
                        component: require('@/views/dashboard/new_order/details/Individual.vue'),
                        orderTypeSequence: [],
                    },
                    {
                        sequence: 14,
                        show: true,
                        enabled: true,
                        name: 'formAndGeometry',
                        get title() { return store.getters.translations.newOrder.details.formAndGeometry.title},
                        component: require('@/views/dashboard/new_order/details/FormAndGeometry.vue'),
                        orderTypeSequence: [],
                    },
                    {
                        sequence: 15,
                        show: true,
                        enabled: true,
                        name: 'frame',
                        get title() { return store.getters.translations.newOrder.details.frame.title},
                        component: require('@/views/dashboard/new_order/details/Frame.vue'),
                        orderTypeSequence: [
                            {orderTypeId: 'S', sequence: 1}
                        ],
                    },
                    {
                        sequence: 16,
                        show: false,
                        enabled: false,
                        name: 'bulkorder',
                        get title() { return store.getters.translations.newOrder.details.bulkorder.title},
                        component: require('@/views/dashboard/new_order/details/Bulkorder.vue'),
                        orderTypeSequence: [],
                    },
                ],
                isComponentEnabled: function(name) {return this.components.some(x => x.name == name ? x.enabled : false)},
                bulkorder: {
                    product: {
                        side: 'B',
                        selectedGroup: null,
                        selectedLensType: null,
                        selectedLensDiameter: null,
                        lensTypeSelection: [],
                        lensDiameterSelection: [],
                    },
                    grid: {
                        isLoading: false,
                        xValues: [],
                        yValues: [],
                        rows: []
                    }
                },
                productCategory: {
                    lensFilterSelection: [],
                    indexSelection: [],
                    corridorSelection: RecType.Design,
                    materialSelection: [],
                    groupSelection: [],
                    specialSelection: [],
                    selectedMaterial: null,
                    selectedGroup: null,
                    selectedLensFilter: null,
                    selectedIndex: null,
                    selectedCorridor: null,
                    selectedSpecial: null,
                    isCorridorDisabled: true,
                    sameIndexBothSides: null
                },
                formAndGeometry: {
                    dbl: null,
                    dblMin: 0,
                    dblMax: 50,
                    frameTypeSelection: [],
                    defaultMinThk: [],
                    bevelPositionSelection: [],
                    selectedFrameType: null,
                    selectedBevelPosition: null,
                    bevelPositionValue: null,
                    grvWidthInput: null,
                    grvDepthInput: null,
                    minEdgeThickness: null,
                    isPolish: false,
                    isEdgePinbevel: false,
                    drillHolesButton: true,
                    fPinbevel: null,
                    pinbevel: null,
                    panto: null,
                    frcv: null,
                    ztilt: null,
                    drillHolesDirectionSelection: ['T','N'],
                    isDrillHolesSameLeftAndRight: false,
                    options: {
                        shapeFromTracerOrDatabase: false,
                        forcedDecentration: false,
                        showLensesScaled1_1: false,
                        drillHoles: false,
                    },
                    drillHoles: [
                        {
                            right: { startX: null, startY: null, endX: null, endY: null, diam: null, depth: null, direction: 'T'},
                            left:  { startX: null, startY: null, endX: null, endY: null, diam: null, depth: null, direction: 'T'},
                        },
                        {
                            right: { startX: null, startY: null, endX: null, endY: null, diam: null, depth: null, direction: 'T'},
                            left:  { startX: null, startY: null, endX: null, endY: null, diam: null, depth: null, direction: 'T'},
                        },
                        {
                            right: { startX: null, startY: null, endX: null, endY: null, diam: null, depth: null, direction: 'T'},
                            left:  { startX: null, startY: null, endX: null, endY: null, diam: null, depth: null, direction: 'T'},
                        },
                        {
                            right: { startX: null, startY: null, endX: null, endY: null, diam: null, depth: null, direction: 'T'},
                            left:  { startX: null, startY: null, endX: null, endY: null, diam: null, depth: null, direction: 'T'},
                        },
                        {
                            right: { startX: null, startY: null, endX: null, endY: null, diam: null, depth: null, direction: 'T'},
                            left:  { startX: null, startY: null, endX: null, endY: null, diam: null, depth: null, direction: 'T'},
                        },
                        {
                            right: { startX: null, startY: null, endX: null, endY: null, diam: null, depth: null, direction: 'T'},
                            left:  { startX: null, startY: null, endX: null, endY: null, diam: null, depth: null, direction: 'T'},
                        },
                    ],
                    drillHolesEditingData: null,
                    right: {
                        isOptimized: false,
                        boxWidth: null,
                        boxHeight: null,
                        decentrationX: null,
                        decentrationY: null,
                        type: 'R',
                        typeAllowedCode: '', //A = Round + Oval, R = Round, O = Oval
                        lensDiameter: null,
                        lensDiameterValues: [],
                        lensDiameterOptimizedValue: 0,
                        opticalDiameter: null,
                        elipticalDiameter: null,
                        ovalMaxDiff: null, //()=> { const formAndGeometry = store.state.moduleSettings; return formAndGeometry.getIniProperties['OVALMAXDIFF']},
                        minimumDiameter: null,
                        thicknessRadioInputData: [
                            {id: 0, get name() {return store.getters.translations.newOrder.details.formAndGeometry.placeholders.minimalEdge}, value: null, min: 0.9, max: 9.9, step: 0.1, decimals: 1},
                            {id: 1, get name() {return store.getters.translations.newOrder.details.formAndGeometry.placeholders.devCenter}, value: null, min: 1, max: 9.9, step: 0.1, decimals: 1},
                        ],
                        selectedThicknessOption: null,
                        basecurve: null,
                        basecurveMin: 0,
                        basecurveMax: 99,
                        forcedDecentration: {
                            decentrationX: null,
                            decentrationXMin: -9.99,
                            decentrationXMax: 9.99,
                            decentrationY: null,
                            decentrationYMin: -9.99,
                            decentrationYMax: 9.99,
                        }
                    },
                    left: {
                        isOptimized: false,
                        boxWidth: null,
                        boxHeight: null,
                        decentrationX: null,
                        decentrationY: null,
                        type: 'R',
                        typeAllowedCode: '', //A = Round + Oval, R = Round, O = Oval
                        lensDiameter: null,
                        lensDiameterValues: [],
                        lensDiameterOptimizedValue: 0,
                        opticalDiameter: null,
                        elipticalDiameter: null,
                        ovalMaxDiff: null, //()=> { const formAndGeometry = store.state.moduleSettings; return formAndGeometry.getIniProperties['OVALMAXDIFF']},
                        minimumDiameter: null,
                        thicknessRadioInputData: [
                            {id: 0, get name() {return store.getters.translations.newOrder.details.formAndGeometry.placeholders.minimalEdge}, value: null, min: 0.9, max: 9.9, step: 0.1, decimals: 1},
                            {id: 1, get name() {return store.getters.translations.newOrder.details.formAndGeometry.placeholders.devCenter}, value: null, min: 1, max: 9.9, step: 0.1, decimals: 1},
                        ],
                        selectedThicknessOption: null,
                        basecurve: null,
                        basecurveMin: 0,
                        basecurveMax: 99,
                        forcedDecentration: {
                            decentrationX: null,
                            decentrationXMin: -9.99,
                            decentrationXMax: 9.99,
                            decentrationY: null,
                            decentrationYMin: -9.99,
                            decentrationYMax: 9.99,
                        }
                    }
                },
                form: {
                    frame: {
                        type: null,
                        data: null,
                        decodedData: null,
                    },
                    shape: {
                        type: null,
                        original: {
                            data: null,
                            decodedData: null,
                        },
                        current: {
                            data: null,
                            decodedData: null
                        }
                    },
                },
                frame: {
                    search: {
                        frameCode: '',
                        model: '',
                        eyeSize: '',
                        dbl: '',
                        color: '',
                        brandSelection: [],
                        selectedBrand: null,
                        selectedFrameType: null,
                    },
                    selectedFrame: null,
                    frameTableData: {
                        headers: [
                            {get label() {return store.getters.translations.newOrder.details.frame.brand}},
                            {get label() {return store.getters.translations.newOrder.details.frame.model}},
                            {get label() {return store.getters.translations.newOrder.details.frame.placeholders.frameCode}},
                            {get label() {return store.getters.translations.newOrder.details.frame.eyeSize}},
                            {get label() {return store.getters.translations.newOrder.details.frame.dbl}},
                            {get label() {return store.getters.translations.newOrder.details.frame.frameType}},
                            {get label() {return store.getters.translations.newOrder.details.frame.color}},
                        ],
                        rows: [],
                        style: {
                            selection: "height: 3.65rem;",
                        }
                    },
                    hasFrameBeenSelected: false,
                    showImage: false,
                    imageUrl: null,
                },
                refraction: {
                    isPrismExpanded: false,
                    isSameLeftRight: false,
                    right: {
                        sphere: null,
                        sphereMin: 0,
                        sphereMax: 0,
                        sphereStep: 0.25,
                        cylinder: null,
                        cylinderMin: 0,
                        cylinderMax: 0,
                        cylinderStep: 0.25,
                        axis: null,
                        axisMin: 0,
                        axisMax: 180,
                        isAxisRequired: false,
                        addition: null,
                        additionMin: 0,
                        additionMax: 0,
                        additionStep: 0.25,
                        pd: null,
                        pdMin: 20,
                        pdMax: 80,
                        isPdRequired: false,
                        height: null,
                        heightMin: 10,
                        heightMax: 60,
                        isHeightRequired: false,
                        prism1: null,
                        prism2: null,
                        prismMin: 0,
                        prismMax: 0,
                        base1Selection: [
                            {get label() {return store.getters.translations.newOrder.details.refraction.base.in000}, value: '000'},
                            {get label() {return store.getters.translations.newOrder.details.refraction.base.out180}, value: '180'},
                            {get label() {return store.getters.translations.newOrder.details.refraction.base.up90}, value: '90'},
                            {get label() {return store.getters.translations.newOrder.details.refraction.base.down270}, value: '270'}
                        ],
                        base2Selection: [
                            {get label() {return store.getters.translations.newOrder.details.refraction.base.in000}, value: '000'},
                            {get label() {return store.getters.translations.newOrder.details.refraction.base.out180}, value: '180'},
                            {get label() {return store.getters.translations.newOrder.details.refraction.base.up90}, value: '90'},
                            {get label() {return store.getters.translations.newOrder.details.refraction.base.down270}, value: '270'}
                        ],
                        base1: null,
                        base2: null,
                        range: null
                    },
                    left: {
                        sphere: null,
                        sphereMin: 0,
                        sphereMax: 0,
                        sphereStep: 0.25,
                        cylinder: null,
                        cylinderMin: 0,
                        cylinderMax: 0,
                        cylinderStep: 0.25,
                        axis: null,
                        axisMin: 0,
                        axisMax: 180,
                        isAxisRequired: false,
                        addition: null,
                        additionMin: 0,
                        additionMax: 0,
                        additionStep: 0.25,
                        pd: null,
                        pdMin: 20,
                        pdMax: 80,
                        isPdRequired: false,
                        height: null,
                        heightMin: 10,
                        heightMax: 60,
                        isHeightRequired: false,
                        prism1: null,
                        prism2: null,
                        prismMin: 0,
                        prismMax: 0,
                        base1Selection: [
                            {get label() {return store.getters.translations.newOrder.details.refraction.base.in180}, value: '180'},
                            {get label() {return store.getters.translations.newOrder.details.refraction.base.out000}, value: '000'},
                            {get label() {return store.getters.translations.newOrder.details.refraction.base.up90}, value: '90'},
                            {get label() {return store.getters.translations.newOrder.details.refraction.base.down270}, value: '270'}
                        ],
                        base2Selection: [
                            {get label() {return store.getters.translations.newOrder.details.refraction.base.in180}, value: '180'},
                            {get label() {return store.getters.translations.newOrder.details.refraction.base.out000}, value: '000'},
                            {get label() {return store.getters.translations.newOrder.details.refraction.base.up90}, value: '90'},
                            {get label() {return store.getters.translations.newOrder.details.refraction.base.down270}, value: '270'}
                        ],
                        base1: null,
                        base2: null,
                        range: null
                    }
                },
                product: {
                    right: {
                        lensTypeSelection: [],
                        selectedLensType: null,
                        hasLens: true,
                        isStock: false,
                        otherSelection: [],
                        selectedOtherList: [],
                    },
                    left: {
                        lensTypeSelection: [],
                        selectedLensType: null,
                        hasLens: true,
                        isStock: false,
                        otherSelection: [],
                        selectedOtherList: [],
                    },
                    tintSelection: [],
                    tintRemark: '',
                    isTintRemarkDisabled: true,
                    tintMandatory: false,
                    antireflectiveCoatingSelection: [],
                    antireflectiveCoatingMandatory: false,
                    uvSelection: [],
                    selectedUv: null,
                    selectedTint: null,
                    selectedAntireflectiveCoating: null,
                    searchInput: '',
                    searchWithFilter: false,
                    isSameLeftRight: true,
                },
                individual: {
                    definedFields: [],
                    right: {
                        type: null,
                        definedFields: [],
                    },
                    left: {
                        type: null,
                        definedFields: [],
                    },
                    fieldMaps: {
                        right: [
                            /*{name: 'CVD', get label() { return store.getters.translations.newOrder.details.individual.placeholders.vertexDistance }, param: 'bvdr',step: 0.1, decimals: 1},
                            {name: 'INSET', get label() { return store.getters.translations.newOrder.details.individual.placeholders.inset }, param: 'insetr', step: 0.1, decimals: 1},*/
                        ],
                        left: [
                            /*{name: 'CVD', get label() { return store.getters.translations.newOrder.details.individual.placeholders.vertexDistance }, param: 'bvdl', step: 0.1, decimals: 1},
                            {name: 'INSET', get label() { return store.getters.translations.newOrder.details.individual.placeholders.inset }, param: 'insetl', step: 0.1, decimals: 1},*/
                        ],
                        common: [
                            /*{name: 'PANTOSCOPIC', get label() { return store.getters.translations.newOrder.details.individual.placeholders.pantoscopicTilt }, param: 'panto', step: 0.1, decimals: 1},
                            {name: 'PROGRESSIONLENGTH', get label() { return store.getters.translations.newOrder.details.individual.placeholders.progressionLength }, param: 'corrlen', step: 1, decimals: 0},
                            {name: 'OBJECTDIST', get label() { return store.getters.translations.newOrder.details.individual.placeholders.objectDistance }, param: 'objectDist', step: 0.01, decimals: 2},
                            {name: 'FACEFORMANGLE', get label() { return store.getters.translations.newOrder.details.individual.placeholders.faceFormAngle }, param: 'ztilt', step: 0.1, decimals: 1},
                            {name: 'INITIAL', get label() { return store.getters.translations.newOrder.details.individual.placeholders.initialEngraving }, param: 'initialgrav', type: 'input'},
                            {name: 'LENTIDIA', get label() { return store.getters.translations.newOrder.details.individual.placeholders.lenticularDiameter }, param: 'lenti', step: 1, decimals: 0},*/
                        ]
                    },
                },
            },
            orderBasket: {
                orderBasketTableData: {
                    headers: [
                        {get label() {return store.getters.translations.orderbasket.table.header.id}, sortParam: 'OrdNumb'},
                        {get label() {return store.getters.translations.orderbasket.table.header.status}, sortParam: 'RecStat'},
                        {get label() {return store.getters.translations.orderbasket.table.header.type}, sortParam: 'RecType'},
                        {get label() {return store.getters.translations.orderbasket.table.header.reference}, sortParam: 'Reference'},
                        {get label() {return store.getters.translations.orderbasket.table.header.frame}, sortParam: 'FReference'},
                        {get label() {return store.getters.translations.orderbasket.table.header.enteredOn}, sortParam: 'EntryDate'},
                    ],
                    rows: []
                },
                selectedOrderBaskets: []
            },
            overview: {
                overviewTableData: {
                    headers: [
                        {get label() {return store.getters.translations.overview.table.header.id}, sortParam: 'ORDNUMB'},
                        {get label() {return store.getters.translations.overview.table.header.type}},
                        {get label() {return store.getters.translations.overview.table.header.reference}, sortParam: 'REF'},
                        {get label() {return store.getters.translations.overview.table.header.lensType}},
                        {get label() {return store.getters.translations.overview.table.header.enteredOn}, sortParam: 'CREATEDATE'},
                        {get label() {return store.getters.translations.overview.table.header.statusManufacturer}, sortParam: 'STATUS'},
                        {get label() {return store.getters.translations.overview.table.header.jobNo}, sortParam: 'ORDNUMBH'},
                    ],
                    rows: [],
                    style: {
                        selection: "height: 4.3rem;"
                    }
                },
            },
            shopConfiguration: {
                userStates: {
                    new: "NEW",
                    editing: "EDITING",
                    readonly: "READONLY",
                },
                currentUserState: null,
                selectedListRow: null,
                formViewUser: null,
                userTableData: {
                    headers: [
                        {get label() {return store.getters.translations.shopConfiguration.table.header.shopNumber}},
                        {get label() {return store.getters.translations.shopConfiguration.table.header.shopName}},
                        {get label() {return store.getters.translations.shopConfiguration.table.header.shopCity}},
                    ],
                    rows: [],
                },
            },
            initializator: {
                skipOnceSelectedFrameFunctionallity: false,
                hasStartDelayFinished: true,
                lensFilter: null,
                lensTypeOnly: null,
                selectLensThrottleTimer: null,
                selectProductCategoryThrottleTimer: null,
                lensGeoThrottleTimerLeft: null,
                lensGeoThrottleTimerRight: null,
                scaleShapeTimer: null,
                currentForm: null,
                currentTrctype: null,
            }
        }
    },
    getters: {
        getMaterials(state){
            return state.erp.materials
        },
        getSpecials(state){
            return state.erp.specials
        },
        getGroups(state){
            return state.erp.groups
        },
        getIndices(state){
            return state.erp.indices
        },
        inTesting(state) {
            return state.inTesting
        },

        // Bevel position
        getBevelPositionAutomatic(state) {
            return state.details.formAndGeometry.bevelPositionSelection.find(x => x && x.control == "7;0") || null
        },
        getBevelPositionManual(state) {
            return state.details.formAndGeometry.bevelPositionSelection.find(x => x && x.control == "0;0") || null
        },
        getBevelPositionFollowFront(state) {
            return state.details.formAndGeometry.bevelPositionSelection.find(x => x && x.control == "1;1") || null
        },
        getBevelPositionFollowBack(state) {
            return state.details.formAndGeometry.bevelPositionSelection.find(x => x && x.control == "5;1") || null
        },
        getBevelPosition3_7(state) {
            return state.details.formAndGeometry.bevelPositionSelection.find(x => x && x.control == "2;0;30") || null
        },
        getBevelPosition4_6(state) {
            return state.details.formAndGeometry.bevelPositionSelection.find(x => x && x.control == "2;0;40") || null
        },
        getBevelPosition5_5(state) {
            return state.details.formAndGeometry.bevelPositionSelection.find(x => x && x.control == "4;0;50") || null
        },
        getBevelPosition6_4(state) {
            return state.details.formAndGeometry.bevelPositionSelection.find(x => x && x.control == "2;0;60") || null
        },
        getBevelPosition7_3(state) {
            return state.details.formAndGeometry.bevelPositionSelection.find(x => x && x.control == "2;0;70") || null
        },
        getBevelPositionFrameCurve(state) {
            return state.details.formAndGeometry.bevelPositionSelection.find(x => x && x.control == "3;1") || null
        },

        // Frame type
        getFrameTypeSelection(state) {
            return state.details.formAndGeometry.frameTypeSelection
        },
        getDefaultMinThk(state) {
          return state.details.formAndGeometry.defaultMinThk['MinThk']
        },
        getFrameTypeMetal(state, getters) {
            return getters.getFrameTypeSelection.find(x => x && x.control == '2;1') || []
        },
        getFrameTypeCell(state, getters) {
            return getters.getFrameTypeSelection.find(x => x && x.control == '1;1') || []
        },
        getFrameTypeRimlessGrooved(state, getters) {
            return getters.getFrameTypeSelection.find(x => x && x.control == '3;3') || []
        },
        getFrameTypeRimlessGroovedDrilled(state, getters) {
            return getters.getFrameTypeSelection.find(x => x && x.control == '3;33') || []
        },
        getFrameTypeRimlessDrilled(state, getters) {
            return getters.getFrameTypeSelection.find(x => x && x.control == '3;2') || []
        },
        getFrameTypeRimlessFlatEdge(state, getters) {
            return getters.getFrameTypeSelection.find(x => x && x.control == '3;40') || []
        },
        getFrameTypeRimlessTitan(state, getters) {
            return getters.getFrameTypeSelection.find(x => x && x.control == '5;1') || []
        },
        getFrameTypeRimlessOptyl(state, getters) {
            return getters.getFrameTypeSelection.find(x => x && x.control == '4;1') || []
        },

        // Order type
        getDefaultOrderType(state, getters) {
            return getters.getStandardLensOrderType
        },
        getStandardLensOrderType(state) {
            return state.basicData.orderTypeSelection.find(x => x.id == 'N')
        },
        getRemoteEdgingOrderType(state) {
            return state.basicData.orderTypeSelection.find(x => x.id == 'E')
        },
        getFrameToComeOrderType(state) {
            return state.basicData.orderTypeSelection.find(x => x.id == 'G')
        },
        getSpectaclesOrderType(state) {
            return state.basicData.orderTypeSelection.find(x => x.id == 'S')
        },
        getBulkorderOrderType(state) {
            return state.basicData.orderTypeSelection.find(x => x.id == 'W')
        },
        isOrderTypeBulkorder(state, getters) {
            const orderType = state.basicData.selectedOrderType
            if (orderType === null || orderType === undefined) return false
            return orderType.id === getters.getBulkorderOrderType.id
        },

        // Order type - title
        getOrderTypeTitle(state, getters) {
            let title = Helpers.getObjProp(state.basicData.selectedOrderType,'title',null)
            if (!title) title = getters.getDefaultOrderType
            return title
        },

        // Detail components list
        getOrderedDetailsComponents(state) {
            let components = state.details.components
            let type = state.basicData.selectedOrderType

            // Sort components
            components.sort((a, b) => {
                let aSequence = a.sequence
                let bSequence = b.sequence

                // Components can be sorted for each order type individually.
                // For example, if the ordertype ID is 'S' (spectacles), the component will load at sequence 1:
                // orderTypeSequence: [{orderTypeId: 'S', sequence: 1}],
                if (type){
                    const getSequence = component => {
                        let x = component.orderTypeSequence.find(x => x.orderTypeId == type.id)
                        return x ? x.sequence : component.sequence
                    }
                    aSequence = getSequence(a)
                    bSequence = getSequence(b)
                }

                return aSequence - bSequence
            });

            return components
        },

        getWasOrderSavedFromFooter(state) {
            return state.dialog.wasOrderSavedFromFooter
        }
    },
    mutations: {
        setDashboardTitle(state, newDashboardTitle) {
            state.dashboardTitle = {
                get title() {
                    let title = newDashboardTitle.title
                    if (typeof title === 'function') title = newDashboardTitle.title()
                    return title
                },
                get subTitle() {
                    let subTitle = newDashboardTitle.subTitle
                    if (typeof subTitle === 'function') subTitle = newDashboardTitle.subTitle()
                    return subTitle
                }
            }
        },
        setWasOrderSavedFromFooter(state, value) {
            state.wasOrderSavedFromFooter = value
        }
    },
    actions: {
        dialog (context, data) {
            if (!data) return

            context.state.dialog.hasDialog = true
            context.state.dialog.title = Helpers.getObjProp(data, 'title', 'Dialog')
            context.state.dialog.description = Helpers.getObjProp(data, 'description')
            context.state.dialog.type = Helpers.getObjProp(data, 'type')
            context.state.dialog.falseButtonText = Helpers.getObjProp(data, 'falseButtonText', 'No')
            context.state.dialog.trueButtonText = Helpers.getObjProp(data, 'trueButtonText', 'Yes')
            context.state.dialog.allowClosing = Helpers.getObjProp(data, 'allowClosing', false)
            context.state.dialog.extraButtons = Helpers.getObjProp(data, 'extraButtons', [])
            context.state.dialog.wasOrderSavedFromFooter = Helpers.getObjProp(data, 'wasOrderSavedFromFooter', false)

            return new Promise(function(resolve) {
                const clearEvents = () => {
                    document.removeEventListener('dialogEventFalse', falseEvent)
                    document.addEventListener('dialogEventTrue', trueEvent)
                    document.addEventListener('dialogEventClose', closeEvent)
                    document.addEventListener('dialogEventsClear', closeEvent)
                    context.state.dialog.hasDialog = false
                }
                document.addEventListener('dialogEventsClear', clearEvents)

                const falseEvent = () => {
                    resolve(false);
                    clearEvents()
                }
                document.addEventListener('dialogEventFalse', falseEvent)

                const trueEvent = () => {
                    resolve(true);
                    clearEvents()
                }
                document.addEventListener('dialogEventTrue', trueEvent)

                const closeEvent = () => {
                    resolve(undefined);
                    clearEvents()
                }
                document.addEventListener('dialogEventClose', closeEvent)
            });
        },
        notify(context, data){
            let message = data
            let seconds = 0
            let type = 'error'

            if (typeof(data) == 'object'){
                if (data.message)
                    message = data.message
                if (data.duration || data.duration > 0)
                    seconds = data.duration
                if (data.type)
                    type = data.type
            }
            context.state.notification.message = message
            context.state.notification.durationSeconds = seconds
            context.state.notification.type = type
            document.dispatchEvent(new Event('notifyEvent'))
        },
        getOrders (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('orders/list', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getOrderbasketOrders (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('orders/orderbasket-list', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        saveOrder (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('orders/save', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        editOrder (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('orders/edit', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        saveBulkorder (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('orders/save-bulkorder', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        editBulkorder (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('orders/edit-bulkorder', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        deleteOrder (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('orders/delete', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        sendOrders (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('orders/send', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        checkOrder (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('orders/check', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        translateIndividual (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('orders/translate-individual', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        translateBulkorderItemList (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('orders/translate-bulkorder-item-list', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
    }
}
