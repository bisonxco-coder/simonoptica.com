
import store from '@/store';
import Helpers from '@/utils/helpers.js';

export default {
    // The fields in state describe all logic for evaluation when saving an order.
    // They must follow the same hierarchichal structure as fields in the orders.js.
    // Getters contain most logic for evaluation cases.
    // Functions in the methods object handle special evaluation.

    state () {
        return {
            details: {
                product: {
                    right: {
                        selectedLensType: {
                            type: 'dropdown',
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {return !isComponentEnabledAndHasLens('product', 'right')},
                            required: (stateField) => {
                                if (stateField.disabled) return false

                                if (store.state.moduleOrders.details.product.right.hasLens) return true
                                return false
                            },
                        }
                    },
                    left: {
                        selectedLensType: {
                            type: 'dropdown',
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {return !isComponentEnabledAndHasLens('product', 'left')},
                            required: (stateField) => {
                                if (stateField.disabled) return false

                                if (store.state.moduleOrders.details.product.left.hasLens) return true
                                return false
                            }
                        }
                    },
                },
                refraction: {
                    right: {
                        sphere: {
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {return !isComponentEnabledAndHasLens('refraction', 'right')},
                            required: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return false

                                if (details.product.right.hasLens) return true
                                return false
                            },
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.refraction.right
                                if (parseFloat(r.cylinder) != 0) {
                                   // check with cylinder power
                                   return true
                                }
                                return isInRangeValidated(r.sphere, r.sphereMin, r.sphereMax)
                            }
                        },
                        cylinder: {
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {return !isComponentEnabledAndHasLens('refraction', 'right')},
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.refraction.right
                                let cylVal = r.cylinder
                                if (parseFloat(cylVal) < 0) {
                                   cylVal = cylVal * -1
                                }
                                return isInRangeValidated(cylVal, r.cylinderMin, r.cylinderMax)
                            }
                        },
                        axis: {
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {return !isComponentEnabledAndHasLens('refraction', 'right')},
                            required: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return false

                                if (details.product.right.hasLens && details.refraction.right.isAxisRequired)
                                    return true
                                return false
                            },
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.refraction.right
                                return isInRangeValidated(r.axis, r.axisMin, r.axisMax)
                            }
                        },
                        addition: {
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {return !isComponentEnabledAndHasLens('refraction', 'right')},
                            required: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return false

                                const min = details.refraction.right.additionMin
                                const max = details.refraction.right.additionMax
                                return hasVal(min) && hasVal(max)
                            },
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.refraction.right
                                const result = isInRangeValidated(r.addition, r.additionMin, r.additionMax)
                                return result
                            }
                        },
                        pd: {
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {
                                if (!isComponentEnabledAndHasLens('refraction', 'right')) return true

                                // Disable if forcedDecentration is on
                                const formAndGeometry = store.state.moduleOrders.details.formAndGeometry
                                if (formAndGeometry.options.forcedDecentration === true) return true

                                return false
                            },
                            required: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return false

                                if (details.product.right.hasLens && details.refraction.right.isPdRequired)
                                    return true
                                return false
                            },
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.refraction.right
                                return isInRangeValidated(r.pd, r.pdMin, r.pdMax)
                            }
                        },
                        height: {
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {
                                if (!isComponentEnabledAndHasLens('refraction', 'right')) return true

                                // Disable if forcedDecentration is on
                                const formAndGeometry = store.state.moduleOrders.details.formAndGeometry
                                if (formAndGeometry.options.forcedDecentration === true) return true

                                return false
                            },
                            required: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return false

                                if (details.product.right.hasLens && details.refraction.right.isHeightRequired)
                                    return true
                                return false
                            },
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.refraction.right
                                return isInRangeValidated(r.height, r.heightMin, r.heightMax)
                            }
                        },
                        prism1: {
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {return !isComponentEnabledAndHasLens('refraction', 'right')},
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.refraction.right
                                return isInRangeValidated(r.prism1, r.prismMin, r.prismMax)
                            }
                        },
                        prism2: {
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {return !isComponentEnabledAndHasLens('refraction', 'right')},
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.refraction.right
                                return isInRangeValidated(r.prism2, r.prismMin, r.prismMax)
                            }
                        }
                    },
                    left: {
                        sphere: {
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {return !isComponentEnabledAndHasLens('refraction', 'left')},
                            required: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return false

                                if (details.product.left.hasLens)return true
                                return false
                            },
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.refraction.left
                                if (parseFloat(r.cylinder) != 0) {
                                   // check with cylinder power
                                   return true
                                }
                                return isInRangeValidated(r.sphere, r.sphereMin, r.sphereMax)
                            }
                        },
                        cylinder: {
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {return !isComponentEnabledAndHasLens('refraction', 'left')},
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.refraction.left
                                let cylVal = r.cylinder
                                if (parseFloat(cylVal) < 0) {
                                   cylVal = cylVal * -1
                                }
                                return isInRangeValidated(cylVal, r.cylinderMin, r.cylinderMax)
                            }
                        },
                        axis: {
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {return !isComponentEnabledAndHasLens('refraction', 'left')},
                            required: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return false

                                if (details.product.left.hasLens && details.refraction.left.isAxisRequired)
                                    return true
                                return false
                            },
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.refraction.left
                                return isInRangeValidated(r.axis, r.axisMin, r.axisMax)
                            }
                        },
                        addition: {
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {return!isComponentEnabledAndHasLens('refraction', 'left')},
                            required: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return false

                                const min = details.refraction.left.additionMin
                                const max = details.refraction.left.additionMax
                                return hasVal(min) && hasVal(max)
                            },
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.refraction.left
                                return isInRangeValidated(r.addition, r.additionMin, r.additionMax)
                            }
                        },
                        pd: {
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {
                                if (!isComponentEnabledAndHasLens('refraction', 'left')) return true

                                // Disable if forcedDecentration is on
                                const formAndGeometry = store.state.moduleOrders.details.formAndGeometry
                                if (formAndGeometry.options.forcedDecentration === true) return true

                                return false
                            },
                            required: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return false

                                if (details.product.left.hasLens && details.refraction.left.isPdRequired)
                                    return true
                                return false
                            },
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.refraction.left
                                return isInRangeValidated(r.pd, r.pdMin, r.pdMax)
                            }
                        },
                        height: {
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {
                                if (!isComponentEnabledAndHasLens('refraction', 'left')) return true

                                // Disable if forcedDecentration is on
                                const formAndGeometry = store.state.moduleOrders.details.formAndGeometry
                                if (formAndGeometry.options.forcedDecentration === true) return true

                                return false
                            },
                            required: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return false

                                if (details.product.left.hasLens && details.refraction.left.isHeightRequired)
                                    return true
                                return false
                            },
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.refraction.left
                                return isInRangeValidated(r.height, r.heightMin, r.heightMax)
                            }
                        },
                        prism1: {
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {return !isComponentEnabledAndHasLens('refraction', 'left')},
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.refraction.left
                                return isInRangeValidated(r.prism1, r.prismMin, r.prismMax)
                            }
                        },
                        prism2: {
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {return !isComponentEnabledAndHasLens('refraction', 'left')},
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.refraction.left
                                return isInRangeValidated(r.prism2, r.prismMin, r.prismMax)
                            }
                        }
                    }
                },
                formAndGeometry: {
                    get isDrillHolesButtonDisabled() {
                        if (!isComponentEnabled('formAndGeometry')) return true

                        const basicData = store.state.moduleOrders.basicData
                        if (basicData.selectedOrderType == null || basicData.selectedOrderType.id === undefined)
                            return true

                        // Disabled for ordertypes: 'Spectacles' and 'Bulkorder'
                        switch (basicData.selectedOrderType.id) {
                            case store.getters.getSpectaclesOrderType.id: return true;
                            case store.getters.getBulkorderOrderType.id: return true;
                        }

                        const frameTypeSelection = store.getters.getFrameTypeSelection
                        if (!frameTypeSelection || frameTypeSelection === undefined || frameTypeSelection == [])
                            return true

                        const formAndGeometry = store.state.moduleOrders.details.formAndGeometry
                        const formAndGeometryProperties = store.state.moduleEvaluator.details.formAndGeometry
                        if (formAndGeometryProperties.selectedFrameType.disabled) return true

                        let type = Helpers.getObjProp(formAndGeometry.selectedFrameType, 'type', false)
                        if (!type) return true
                        switch (type) {
                            case store.getters.getFrameTypeMetal.type: return true;
                            case store.getters.getFrameTypeCell.type: return true;
                            case store.getters.getFrameTypeRimlessGrooved.type: return true;
                            case store.getters.getFrameTypeRimlessGroovedDrilled.type: return false;
                            case store.getters.getFrameTypeRimlessDrilled.type: return false;
                            case store.getters.getFrameTypeRimlessFlatEdge.type: return true;
                        }
                        return true
                    },
                    get isDeleteFormButtonDisabled() {
                        if (!isComponentEnabled('formAndGeometry')) return true

                        const basicData = store.state.moduleOrders.basicData
                        if (basicData.selectedOrderType == null || basicData.selectedOrderType.id === undefined)
                            return true

                        // Disabled for ordertypes: 'Spectacles' and 'Bulkorder'
                        switch (basicData.selectedOrderType.id) {
                            case store.getters.getSpectaclesOrderType.id: return true;
                            case store.getters.getBulkorderOrderType.id: return true;
                        }

                        const form = store.state.moduleOrders.details.form
                        if (!form.shape.current.decodedData) return true

                        return false
                    },
                    get isRefreshFormButtonDisabled() {
                        if (!isComponentEnabled('formAndGeometry')) return true

                        const basicData = store.state.moduleOrders.basicData
                        if (basicData.selectedOrderType == null || basicData.selectedOrderType.id === undefined)
                            return true

                        // Disabled for ordertypes: 'Spectacles' and 'Bulkorder'
                        switch (basicData.selectedOrderType.id) {
                            case store.getters.getSpectaclesOrderType.id: return true;
                            case store.getters.getBulkorderOrderType.id: return true;
                        }

                        const form = store.state.moduleOrders.details.form
                        if (!form.shape.current.decodedData) return true

                        return false
                    },
                    selectedFrameType: {
                        get disabled() {
                            if (!isComponentEnabled('formAndGeometry')) return true

                            const basicData = store.state.moduleOrders.basicData
                            if (basicData.selectedOrderType == null || basicData.selectedOrderType.id === undefined)
                                return true

                            // Disable for ordertype 'Spectacles'
                            if (basicData.selectedOrderType.id == store.getters.getSpectaclesOrderType.id)
                                return true

                            return false
                        }
                    },
                    selectedBevelPosition: {
                        get disabled() {
                            if (!isComponentEnabled('formAndGeometry')) return true

                            const basicData = store.state.moduleOrders.basicData
                            if (basicData.selectedOrderType == null || basicData.selectedOrderType.id === undefined)
                                return true

                            // Disable for ordertype 'Spectacles'
                            if (basicData.selectedOrderType.id == store.getters.getSpectaclesOrderType.id)
                                return true

                            const frameTypeSelection = store.getters.getFrameTypeSelection
                            if (!frameTypeSelection || frameTypeSelection === undefined || frameTypeSelection == [])
                                return true

                            return false
                        }
                    },
                    bevelPositionValue: {
                        get resetIfDisabled() {return true},
                        get disabled() {
                            if (!isComponentEnabled('formAndGeometry')) return true

                            const basicData = store.state.moduleOrders.basicData
                            if (basicData.selectedOrderType == null || basicData.selectedOrderType.id === undefined)
                                return true

                            // Disable for ordertype 'Spectacles'
                            if (basicData.selectedOrderType.id == store.getters.getSpectaclesOrderType.id)
                                return true

                            const formAndGeometry = store.state.moduleOrders.details.formAndGeometry
                            const formAndGeometryProperties = store.state.moduleEvaluator.details.formAndGeometry

                            if (formAndGeometryProperties.selectedBevelPosition.disabled) return true
                            if (!formAndGeometry.selectedBevelPosition) return true
                            return +formAndGeometry.selectedBevelPosition.control.split(';')[1] ? false : true
                        }
                    },
                    isEdgePinbevel: {
                        get disabled() {
                            if (!isComponentEnabled('formAndGeometry')) return true

                            const basicData = store.state.moduleOrders.basicData
                            if (basicData.selectedOrderType == null || basicData.selectedOrderType.id === undefined)
                                return true

                            // Disable for ordertype 'Spectacles'
                            if (basicData.selectedOrderType.id == store.getters.getSpectaclesOrderType.id)
                                return true

                            return false
                        }
                    },
                    grvWidthInput: {
                        get resetIfDisabled() {return true},
                        get disabled() {
                            if (!isComponentEnabled('formAndGeometry')) return true

                            const frameTypeSelection = store.getters.getFrameTypeSelection
                            if (!frameTypeSelection || frameTypeSelection === undefined || frameTypeSelection == [])
                                return true

                            const formAndGeometry = store.state.moduleOrders.details.formAndGeometry
                            let type = Helpers.getObjProp(formAndGeometry.selectedFrameType, 'type', false)
                            if (!type) return true
                            switch (type) {
                                case store.getters.getFrameTypeMetal.type: return true;
                                case store.getters.getFrameTypeCell.type: return true;
                                case store.getters.getFrameTypeRimlessGrooved.type: return false;
                                case store.getters.getFrameTypeRimlessGroovedDrilled.type: return false;
                                case store.getters.getFrameTypeRimlessDrilled.type: return true;
                                case store.getters.getFrameTypeRimlessFlatEdge.type: return true;
                            }
                            return true
                        }
                    },
                    grvDepthInput: {
                        get resetIfDisabled() {return true},
                        get disabled() {
                            if (!isComponentEnabled('formAndGeometry')) return true

                            const frameTypeSelection = store.getters.getFrameTypeSelection
                            if (!frameTypeSelection || frameTypeSelection === undefined || frameTypeSelection == [])
                                return true

                            const formAndGeometry = store.state.moduleOrders.details.formAndGeometry
                            let type = Helpers.getObjProp(formAndGeometry.selectedFrameType, 'type', false)
                            if (!type) return true
                            switch (type) {
                                case store.getters.getFrameTypeMetal.type: return true;
                                case store.getters.getFrameTypeCell.type: return true;
                                case store.getters.getFrameTypeRimlessGrooved.type: return false;
                                case store.getters.getFrameTypeRimlessGroovedDrilled.type: return false;
                                case store.getters.getFrameTypeRimlessDrilled.type: return true;
                                case store.getters.getFrameTypeRimlessFlatEdge.type: return true;
                            }
                            return true
                        }
                    },
                    dbl: {
                        errorMessage: "",
                        required: (stateField) => {
                            const hasFormShape = () => (
                                store.state.moduleOrders.details.form.shape.current.decodedData !== undefined &&
                                store.state.moduleOrders.details.form.shape.current.decodedData !== null &&
                                store.state.moduleOrders.details.form.shape.current.decodedData !== ""
                            )
                            if (hasFormShape()) return true
                            return false
                        },
                        get resetIfDisabled() {return false},
                        get disabled () {
                            if (!isComponentEnabled('formAndGeometry')) return true

                            const basicData = store.state.moduleOrders.basicData
                            if (basicData.selectedOrderType == null || basicData.selectedOrderType.id === undefined)
                                return true

                            // Disable for ordertype 'Spectacle'
                            if (basicData.selectedOrderType.id == store.getters.getSpectaclesOrderType.id)
                                return true
                            return false
                        },
                    },
                    options: {
                        shapeFromTracerOrDatabase: {
                            get resetIfDisabled() {return true},
                            get disabled () {
                                if (!isComponentEnabled('formAndGeometry')) return true

                                const basicData = store.state.moduleOrders.basicData
                                if (basicData.selectedOrderType == null || basicData.selectedOrderType.id === undefined)
                                    return true

                                // Enable only for ordertypes:
                                // 'Standard lens', 'Remote edging' and 'Frame to come',
                                switch (basicData.selectedOrderType.id) {
                                    case store.getters.getStandardLensOrderType.id: return false;
                                    case store.getters.getRemoteEdgingOrderType.id: return false;
                                    case store.getters.getFrameToComeOrderType.id: return false;
                                }
                                return true
                            },
                        },
                        forcedDecentration: {
                            get resetIfDisabled() {return true},
                            get disabled () {
                                if (!isComponentEnabled('formAndGeometry')) return true

                                const basicData = store.state.moduleOrders.basicData
                                if (basicData.selectedOrderType == null || basicData.selectedOrderType.id === undefined)
                                    return true

                                // Disable if ordertype is Spectacles
                                if (basicData.selectedOrderType.id == store.getters.getSpectaclesOrderType.id)
                                    return true
                                return false
                            },
                        }
                    },
                    right: {
                        boxWidth: {
                            get resetIfDisabled() {return false},
                            get disabled() {
                                if (!isComponentEnabledAndHasLens('formAndGeometry', 'right')) return true

                                const basicData = store.state.moduleOrders.basicData
                                if (!basicData.selectedOrderType ||
                                    basicData.selectedOrderType === undefined ||
                                    !basicData.selectedOrderType.id ||
                                    basicData.selectedOrderType.id === undefined
                                ) return true

                                // Disable for ordertypes: 'Spectacles' and 'Bulk order
                                switch (basicData.selectedOrderType.id) {
                                    case store.getters.getSpectaclesOrderType.id: return true;
                                    case store.getters.getBulkorderOrderType.id: return true;
                                }
                                return false

                                // Handle 'Remote edging' and 'Frame to come' based on the selected Frame type
                                // Disable if there are no frame types
                                const frameTypeSelection = store.getters.getFrameTypeSelection
                                const selectedFrameType = store.state.moduleOrders.details.formAndGeometry.selectedFrameType
                                if (!frameTypeSelection || frameTypeSelection === undefined || frameTypeSelection == [])
                                    return true
                                if (!selectedFrameType || selectedFrameType === undefined || selectedFrameType == [])
                                    return true

                                // Allow only for Rimless drilled or Rimless(Flat edge)
                                switch (selectedFrameType.control) {
                                    case store.getters.getFrameTypeMetal.control: return true;
                                    case store.getters.getFrameTypeCell.control: return true;
                                    case store.getters.getFrameTypeRimlessGrooved.control: return true;
                                    case store.getters.getFrameTypeRimlessGroovedDrilled.control: return true;
                                    case store.getters.getFrameTypeRimlessDrilled.control: return false;
                                    case store.getters.getFrameTypeRimlessFlatEdge.control: return false;
                                }
                                return true
                            },
                        },
                        boxHeight: {
                            get resetIfDisabled() {return false},
                            get disabled() {
                                if (!isComponentEnabledAndHasLens('formAndGeometry', 'right')) return true

                                const basicData = store.state.moduleOrders.basicData
                                if (!basicData.selectedOrderType ||
                                    basicData.selectedOrderType === undefined ||
                                    !basicData.selectedOrderType.id ||
                                    basicData.selectedOrderType.id === undefined
                                ) return true

                                // Disable for ordertypes: 'Spectacles' and 'Bulk order
                                switch (basicData.selectedOrderType.id) {
                                    case store.getters.getSpectaclesOrderType.id: return true;
                                    case store.getters.getBulkorderOrderType.id: return true;
                                }
                                return false

                                // Handle 'Remote edging' and 'Frame to come' based on the selected Frame type
                                // Disable if there are no frame types
                                const frameTypeSelection = store.getters.getFrameTypeSelection
                                const selectedFrameType = store.state.moduleOrders.details.formAndGeometry.selectedFrameType
                                if (!frameTypeSelection || frameTypeSelection === undefined || frameTypeSelection == [])
                                    return true
                                if (!selectedFrameType || selectedFrameType === undefined || selectedFrameType == [])
                                    return true

                                // Allow only for Rimless drilled or Rimless(Flat edge)
                                switch (selectedFrameType.control) {
                                    case store.getters.getFrameTypeMetal.control: return true;
                                    case store.getters.getFrameTypeCell.control: return true;
                                    case store.getters.getFrameTypeRimlessGrooved.control: return false;
                                    case store.getters.getFrameTypeRimlessGroovedDrilled.control: return false;
                                    case store.getters.getFrameTypeRimlessDrilled.control: return false;
                                    case store.getters.getFrameTypeRimlessFlatEdge.control: return false;
                                }
                                return true
                            },
                        },
                        lensDiameter: {
                            errorMessage: "",
                            get resetIfDisabled() {
                                return !isComponentEnabledAndHasLens('formAndGeometry', 'right')
                            },
                            get disabled() {
                                const details = store.state.moduleOrders.details
                                return !isComponentEnabledAndHasLens('formAndGeometry', 'right') || details.formAndGeometry.right.isOptimized
                            },
                            required: (stateField) => {
                                if (stateField.disabled) return false
                                return true
                            },
                            isLensDiameterGreaterThanMinimum: (stateField) => {
                                if (stateField.disabled) return true

                                const details = store.state.moduleOrders.details
                                let minimumDiameter = details.formAndGeometry.right.minimumDiameter
                                let diameter = details.formAndGeometry.right.lensDiameter

                                if (!minimumDiameter) return true

                                minimumDiameter = parseFloat(minimumDiameter)
                                diameter = parseFloat(diameter)

                                return diameter >= minimumDiameter
                            }
                        },
                        opticalDiameter: {
                            get resetIfDisabled() { return false },
                            get disabled() { return true }
                        },
                        elipticalDiameter: {
                            errorMessage: "",
                            get resetIfDisabled() { return false },
                            get disabled() {
                                const formAndGeometry = store.state.moduleSettings;
                                if(formAndGeometry.getIniProperties !== null){
                                    return formAndGeometry.getIniProperties['OVALDM'] !== 'YES';
                                }
                                return false;
                            },
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.formAndGeometry.right
                                const formAndGeometry = store.state.moduleSettings;
                                return isInRangeValidated(r.elipticalDiameter, r.opticalDiameter - formAndGeometry.getIniProperties['OVALMAXDIFF'], r.opticalDiameter - 1)
                            },
                            get displayed() {
                                const type = store.state.moduleOrders.details.formAndGeometry.right.type
                                const hasLensDiameterValues = listHasValues(store.state.moduleOrders.details.formAndGeometry.right.lensDiameterValues)
                                return hasLensDiameterValues && type === 'O'
                            }
                        },
                        isOptimized: {
                            get disabled() {
                                const hasFormShape = () => (
                                    store.state.moduleOrders.details.form.shape.current.decodedData !== undefined &&
                                    store.state.moduleOrders.details.form.shape.current.decodedData !== null &&
                                    store.state.moduleOrders.details.form.shape.current.decodedData !== ""
                                )
                                const isStock = store.state.moduleOrders.details.product.right.isStock

                                // Get CTOAllowed flag from ranges and combinations response
                                let isCTOAllowed = true
                                const rangesCombinations = store.state.moduleOrders.erp.getRangesCombination
                                if (rangesCombinations !== undefined && rangesCombinations !== null && Object.entries(rangesCombinations).length > 0) {

                                    let CTOAllowed = Helpers.getObjProp(rangesCombinations, 'CTOAllowed', null)
                                    if (CTOAllowed !== null && CTOAllowed !== undefined && CTOAllowed !== ''){
                                        CTOAllowed = CTOAllowed.toLowerCase()

                                        isCTOAllowed = false
                                        if (CTOAllowed === "yes")
                                            isCTOAllowed = true
                                    }
                                }
                                return !isComponentEnabledAndHasLens('formAndGeometry', 'right') || !hasFormShape() || isStock || !isCTOAllowed
                            }
                        },
                        basecurve: {
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {
                                const isStock = store.state.moduleOrders.details.product.right.isStock
                                return !isComponentEnabledAndHasLens('formAndGeometry', 'right') || isStock
                            },
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.formAndGeometry.right
                                return isInRangeValidated(r.basecurve, r.basecurveMin, r.basecurveMax)
                            }
                        },
                        selectedThicknessOption: {
                            errorMessage: "",
                            get disabled() {
                                const isStock = store.state.moduleOrders.details.product.right.isStock
                                return !isComponentEnabledAndHasLens('formAndGeometry', 'right') || isStock
                            },
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.formAndGeometry.right.selectedThicknessOption
                                return isInRangeValidated(r.value, r.min, r.max)
                            }
                        },
                        forcedDecentration: {
                            decentrationX: {
                                errorMessage: "",
                                get disabled () {
                                    if (!isComponentEnabled('formAndGeometry')) return true

                                    const basicData = store.state.moduleOrders.basicData
                                    if (basicData.selectedOrderType == null || basicData.selectedOrderType.id === undefined)
                                        return true

                                    // Disable if ordertype is Spectacles
                                    if (basicData.selectedOrderType.id == store.getters.getSpectaclesOrderType.id)
                                        return true
                                    return false
                                },
                                inRange: (stateField) => {
                                    if (stateField.disabled) return true

                                    // Prevent checking if forcedDecentration is off
                                    const formAndGeometry = store.state.moduleOrders.details.formAndGeometry
                                    if (formAndGeometry.options.forcedDecentration !== true) return true

                                    const forcedDecentration = formAndGeometry.right.forcedDecentration
                                    const value = forcedDecentration.decentrationX
                                    const min = forcedDecentration.decentrationXMin
                                    const max = forcedDecentration.decentrationXMax
                                    return isInRangeValidated(value, min, max)
                                },
                                required: (stateField) => {
                                    if (stateField.disabled) return false

                                    // Prevent checking if forcedDecentration is off
                                    const formAndGeometry = store.state.moduleOrders.details.formAndGeometry
                                    if (formAndGeometry.options.forcedDecentration !== true) return false

                                    return true
                                }
                            },
                            decentrationY: {
                                errorMessage: "",
                                get disabled () {
                                    if (!isComponentEnabled('formAndGeometry')) return true

                                    const basicData = store.state.moduleOrders.basicData
                                    if (basicData.selectedOrderType == null || basicData.selectedOrderType.id === undefined)
                                        return true

                                    // Disable if ordertype is Spectacles
                                    if (basicData.selectedOrderType.id == store.getters.getSpectaclesOrderType.id)
                                        return true
                                    return false
                                },
                                inRange: (stateField) => {
                                    if (stateField.disabled) return true

                                    // Prevent checking if forcedDecentration is off
                                    const formAndGeometry = store.state.moduleOrders.details.formAndGeometry
                                    if (formAndGeometry.options.forcedDecentration !== true) return true

                                    const forcedDecentration = formAndGeometry.right.forcedDecentration
                                    const value = forcedDecentration.decentrationY
                                    const min = forcedDecentration.decentrationYMin
                                    const max = forcedDecentration.decentrationYMax
                                    return isInRangeValidated(value, min, max)
                                },
                                required: (stateField) => {
                                    if (stateField.disabled) return false

                                    // Prevent checking if forcedDecentration is off
                                    const formAndGeometry = store.state.moduleOrders.details.formAndGeometry
                                    if (formAndGeometry.options.forcedDecentration !== true) return false

                                    return true
                                }
                            }
                        }
                    },
                    left: {
                        boxWidth: {
                            get resetIfDisabled() {return false},
                            get disabled() {
                                if (!isComponentEnabledAndHasLens('formAndGeometry', 'left')) return true

                                const basicData = store.state.moduleOrders.basicData
                                if (!basicData.selectedOrderType ||
                                    basicData.selectedOrderType === undefined ||
                                    !basicData.selectedOrderType.id ||
                                    basicData.selectedOrderType.id === undefined
                                ) return true

                                // Disable for ordertypes: 'Spectacles' and 'Bulk order
                                switch (basicData.selectedOrderType.id) {
                                    case store.getters.getSpectaclesOrderType.id: return true;
                                    case store.getters.getBulkorderOrderType.id: return true;
                                }
                                return false

                                // Handle 'Remote edging' and 'Frame to come' based on the selected Frame type
                                // Disable if there are no frame types
                                const frameTypeSelection = store.getters.getFrameTypeSelection
                                const selectedFrameType = store.state.moduleOrders.details.formAndGeometry.selectedFrameType
                                if (!frameTypeSelection || frameTypeSelection === undefined || frameTypeSelection == [])
                                    return true
                                if (!selectedFrameType || selectedFrameType === undefined || selectedFrameType == [])
                                    return true

                                // Allow only for Rimless drilled or Rimless(Flat edge)
                                switch (selectedFrameType.control) {
                                    case store.getters.getFrameTypeMetal.control: return true;
                                    case store.getters.getFrameTypeCell.control: return true;
                                    case store.getters.getFrameTypeRimlessGrooved.control: return true;
                                    case store.getters.getFrameTypeRimlessGroovedDrilled.control: return true;
                                    case store.getters.getFrameTypeRimlessDrilled.control: return false;
                                    case store.getters.getFrameTypeRimlessFlatEdge.control: return false;
                                }
                                return true
                            },
                        },
                        boxHeight: {
                            get resetIfDisabled() {return false},
                            get disabled() {
                                if (!isComponentEnabledAndHasLens('formAndGeometry', 'left')) return true

                                const basicData = store.state.moduleOrders.basicData
                                if (!basicData.selectedOrderType ||
                                    basicData.selectedOrderType === undefined ||
                                    !basicData.selectedOrderType.id ||
                                    basicData.selectedOrderType.id === undefined
                                ) return true

                                // Disable for ordertypes: 'Spectacles' and 'Bulk order
                                switch (basicData.selectedOrderType.id) {
                                    case store.getters.getSpectaclesOrderType.id: return true;
                                    case store.getters.getBulkorderOrderType.id: return true;
                                }
                                return false

                                // Handle 'Remote edging' and 'Frame to come' based on the selected Frame type
                                // Disable if there are no frame types
                                const frameTypeSelection = store.getters.getFrameTypeSelection
                                const selectedFrameType = store.state.moduleOrders.details.formAndGeometry.selectedFrameType
                                if (!frameTypeSelection || frameTypeSelection === undefined || frameTypeSelection == [])
                                    return true
                                if (!selectedFrameType || selectedFrameType === undefined || selectedFrameType == [])
                                    return true

                                // Allow only for Rimless drilled or Rimless(Flat edge)
                                switch (selectedFrameType.control) {
                                    case store.getters.getFrameTypeMetal.control: return true;
                                    case store.getters.getFrameTypeCell.control: return true;
                                    case store.getters.getFrameTypeRimlessGrooved.control: return false;
                                    case store.getters.getFrameTypeRimlessGroovedDrilled.control: return false;
                                    case store.getters.getFrameTypeRimlessDrilled.control: return false;
                                    case store.getters.getFrameTypeRimlessFlatEdge.control: return false;
                                }
                                return true
                            },
                        },
                        lensDiameter: {
                            errorMessage: "",
                            get resetIfDisabled() {
                                return !isComponentEnabledAndHasLens('formAndGeometry', 'left')
                            },
                            get disabled() {
                                const details = store.state.moduleOrders.details
                                return !isComponentEnabledAndHasLens('formAndGeometry', 'left') || details.formAndGeometry.left.isOptimized
                            },
                            required: (stateField) => {
                                if (stateField.disabled) return false
                                return true
                            },
                            isLensDiameterGreaterThanMinimum: (stateField) => {
                                if (stateField.disabled) return true

                                const details = store.state.moduleOrders.details
                                let minimumDiameter = details.formAndGeometry.left.minimumDiameter
                                let diameter = details.formAndGeometry.left.lensDiameter

                                if (!minimumDiameter) return true

                                minimumDiameter = parseFloat(minimumDiameter)
                                diameter = parseFloat(diameter)

                                return diameter >= minimumDiameter
                            }
                        },
                        opticalDiameter: {
                            get resetIfDisabled() { return false },
                            get disabled() { return true }
                        },
                        elipticalDiameter: {
                            errorMessage: "",
                            get resetIfDisabled() { return false },
                            get disabled() {
                                const formAndGeometry = store.state.moduleSettings;
                                if(formAndGeometry.getIniProperties !== null){
                                    return formAndGeometry.getIniProperties['OVALDM'] !== 'YES';
                                }
                                return false;
                            },
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let l = details.formAndGeometry.left
                                const formAndGeometry = store.state.moduleSettings;
                                return isInRangeValidated(l.elipticalDiameter, l.opticalDiameter - formAndGeometry.getIniProperties['OVALMAXDIFF'], l.opticalDiameter - 1)
                            },
                            get displayed() {
                                const type = store.state.moduleOrders.details.formAndGeometry.left.type
                                const hasLensDiameterValues = listHasValues(store.state.moduleOrders.details.formAndGeometry.left.lensDiameterValues)
                                return hasLensDiameterValues && type === 'O'
                            }
                        },
                        isOptimized: {
                            get disabled() {
                                const hasFormShape = () => (
                                    store.state.moduleOrders.details.form.shape.current.decodedData !== undefined &&
                                    store.state.moduleOrders.details.form.shape.current.decodedData !== null &&
                                    store.state.moduleOrders.details.form.shape.current.decodedData !== ""
                                )
                                const isStock = store.state.moduleOrders.details.product.left.isStock

                                // Get CTOAllowed flag from ranges and combinations response
                                let isCTOAllowed = true
                                const rangesCombinations = store.state.moduleOrders.erp.getRangesCombination
                                if (rangesCombinations !== undefined && rangesCombinations !== null && Object.entries(rangesCombinations).length > 0) {

                                    let CTOAllowed = Helpers.getObjProp(rangesCombinations, 'CTOAllowed', null)
                                    if (CTOAllowed !== null && CTOAllowed !== undefined && CTOAllowed !== ''){
                                        CTOAllowed = CTOAllowed.toLowerCase()

                                        isCTOAllowed = false
                                        if (CTOAllowed === "yes")
                                            isCTOAllowed = true
                                    }
                                }

                                return !isComponentEnabledAndHasLens('formAndGeometry', 'left') || !hasFormShape() || isStock || !isCTOAllowed
                            }
                        },
                        basecurve: {
                            errorMessage: "",
                            get resetIfDisabled() {return true},
                            get disabled() {
                                const isStock = store.state.moduleOrders.details.product.left.isStock
                                return !isComponentEnabledAndHasLens('formAndGeometry', 'left') || isStock
                            },
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.formAndGeometry.left
                                return isInRangeValidated(r.basecurve, r.basecurveMin, r.basecurveMax)
                            }
                        },
                        selectedThicknessOption: {
                            errorMessage: "",
                            get disabled() {
                                const isStock = store.state.moduleOrders.details.product.left.isStock
                                return !isComponentEnabledAndHasLens('formAndGeometry', 'left') || isStock
                            },
                            inRange: (stateField) => {
                                let details = store.state.moduleOrders.details
                                if (stateField.disabled) return true

                                let r = details.formAndGeometry.left.selectedThicknessOption
                                return isInRangeValidated(r.value, r.min, r.max)
                            }
                        },
                        forcedDecentration: {
                            decentrationX: {
                                errorMessage: "",
                                get disabled () {
                                    if (!isComponentEnabled('formAndGeometry')) return true

                                    const basicData = store.state.moduleOrders.basicData
                                    if (basicData.selectedOrderType == null || basicData.selectedOrderType.id === undefined)
                                        return true

                                    // Disable if ordertype is Spectacles
                                    if (basicData.selectedOrderType.id == store.getters.getSpectaclesOrderType.id)
                                        return true
                                    return false
                                },
                                inRange: (stateField) => {
                                    if (stateField.disabled) return true

                                    // Prevent checking if forcedDecentration is off
                                    const formAndGeometry = store.state.moduleOrders.details.formAndGeometry
                                    if (formAndGeometry.options.forcedDecentration !== true) return true

                                    const forcedDecentration = formAndGeometry.left.forcedDecentration
                                    const value = forcedDecentration.decentrationX
                                    const min = forcedDecentration.decentrationXMin
                                    const max = forcedDecentration.decentrationXMax
                                    return isInRangeValidated(value, min, max)
                                },
                                required: (stateField) => {
                                    if (stateField.disabled) return false

                                    // Prevent checking if forcedDecentration is off
                                    const formAndGeometry = store.state.moduleOrders.details.formAndGeometry
                                    if (formAndGeometry.options.forcedDecentration !== true) return false

                                    return true
                                }
                            },
                            decentrationY: {
                                errorMessage: "",
                                get disabled () {
                                    if (!isComponentEnabled('formAndGeometry')) return true

                                    const basicData = store.state.moduleOrders.basicData
                                    if (basicData.selectedOrderType == null || basicData.selectedOrderType.id === undefined)
                                        return true

                                    // Disable if ordertype is Spectacles
                                    if (basicData.selectedOrderType.id == store.getters.getSpectaclesOrderType.id)
                                        return true
                                    return false
                                },
                                inRange: (stateField) => {
                                    if (stateField.disabled) return true

                                    // Prevent checking if forcedDecentration is off
                                    const formAndGeometry = store.state.moduleOrders.details.formAndGeometry
                                    if (formAndGeometry.options.forcedDecentration !== true) return true

                                    const forcedDecentration = formAndGeometry.left.forcedDecentration
                                    const value = forcedDecentration.decentrationY
                                    const min = forcedDecentration.decentrationYMin
                                    const max = forcedDecentration.decentrationYMax
                                    return isInRangeValidated(value, min, max)
                                },
                                required: (stateField) => {
                                    if (stateField.disabled) return false

                                    // Prevent checking if forcedDecentration is off
                                    const formAndGeometry = store.state.moduleOrders.details.formAndGeometry
                                    if (formAndGeometry.options.forcedDecentration !== true) return false

                                    return true
                                }
                            }
                        }
                    }
                },
                individual: {
                    // This method gets assigned to every individual component field
                    evaluation: (field, state) => {
                        let details = store.state.moduleOrders.details
                        let isComponentEnabled = details.isComponentEnabled('individual') ? true : false
                        if (!isComponentEnabled) return true

                        // Initial engraving evaluation (error message is assigned inside the function)
                        if (!state.methods.initialEngravingEvaluation()) return false

                        // Required evaluation
                        if (field.required && (field.value === undefined || !field.value)){
                            field.errorMessage = store.getters.translations.notifications.newValue
                            if (field.component == 'select')
                                field.errorMessage = store.getters.translations.notifications.selectValue
                            return false
                        }

                        // In range evaluation
                        if (field.value && field.value !== undefined &&
                            field.min && field.min !== undefined &&
                            field.max && field.max !== undefined &&
                            !isInRangeValidated(field.value, field.min, field.max)
                        ){
                            field.errorMessage = store.getters.translations.notifications.outOfDelivery
                            return false
                        }

                        return true
                    }
                }
            },
            methods: {
                initialEngravingEvaluation() {
                    // Evaluate individual component -> initial engraving field
                    // Check if field contains allowed characters and fits in length
                    // Return false is error, true is ok
                    try {
                        const individual = store.state.moduleOrders.details.individual
                        if (!individual.definedFields) return true

                        const initial = individual.definedFields.find(x => x.name == 'INITIAL')
                        if (!initial || !initial.value || initial.value === '') return true

                        initial.errorMessage = ""

                        // Allowed characters evaluation
                        let areCharactersValid = true
                        let initialAllowedCharacters = Helpers.getObjProp(initial.data, 'MIN', null)
                        if (!initialAllowedCharacters)
                            initialAllowedCharacters = Helpers.getObjProp(initial.data, 'MAX', null)
                        try {
                            // Only characters in initialAllowedCharacters are allowed
                            if (initialAllowedCharacters){
                                areCharactersValid = initial.value.split('').every(char => {
                                    return initialAllowedCharacters.includes(char)
                                })
                            }
                        } catch (error) {
                            console.error(error)
                        }
                        if (!areCharactersValid) {
                            initial.errorMessage = store.getters.translations.notifications.allowedCharacters + initialAllowedCharacters
                            return false
                        }

                        // Allowed length of characters evaluation
                        let initialLength = Helpers.getObjProp(initial.data, 'LENGTH', 99999999)
                        let isCharacterLengthValid = initial.value.length <= initialLength
                        if (!isCharacterLengthValid) {
                            initial.errorMessage = store.getters.translations.notifications.allowedNumberCharacters + initialLength
                            return false
                        }
                    } catch (error) {
                        console.error(error)
                    }
                    return true
                },
                cylinderSpherePowerEvaluation(side, diamlist) {
                    // Evaluate sphere and cylinder with cylinder power

                    let refraction = store.state.moduleOrders.details.refraction
                    let formAndGeometry = store.state.moduleOrders.details.formAndGeometry
                    let refractionProperties = store.state.moduleEvaluator.details.refraction

                    if (!diamlist || diamlist == [] || diamlist.length == 0 || !side)
                        return

                    let cylinder = Helpers.parseOrNull(refraction[side].cylinder)
                    let cylinderMin = Helpers.parseOrNull(refraction[side].cylinderMin)
                    let cylinderMax = Helpers.parseOrNull(refraction[side].cylinderMax)
                    let sphere = Helpers.parseOrNull(refraction[side].sphere)
                    let sphereMin = Helpers.parseOrNull(refraction[side].sphereMin)
                    let sphereMax = Helpers.parseOrNull(refraction[side].sphereMax)
                    let powerMinNew = sphereMin
                    let powerMaxNew = sphereMax

                    // Return if at least 1 required value is null
                    let hasNull = [cylinder, cylinderMin, cylinderMax, sphere, sphereMin, sphereMax].some(x => x == null)
                    if (hasNull) return

                    if (cylinder < 0) {
                        sphere = sphere + cylinder
                        cylinder = cylinder * -1
                    }

                    // Cylinder power evaluation
                    let rangeFound = false
                    for (var i = 0; i < diamlist.length; ++i) {
                        let powerMin = diamlist[i].CYLPOWERFR
                        let powerMax = diamlist[i].CYLPOWERTO

                        if (powerMin > 0 && sphere <= sphereMin)
                            powerMinNew = powerMinNew - cylinder * powerMin / 100

                        if (powerMax > 0 && sphere >= (powerMaxNew - cylinderMax * powerMax / 100))
                            powerMaxNew = powerMaxNew - cylinder * powerMax / 100

                        if (sphere <= powerMaxNew && sphere >= powerMinNew)
                            rangeFound = true
                    }

                    // Raise error if cylinder power isn't in range
                    // Hide round - oval selection and prevent inputing lens diameter in FormAndGeometry form
                    let errorMessage = store.getters.translations.notifications.cylinderPowerOut
                    store.getters.translations.notifications.allowedNumberCharacters
                    refractionProperties[side].sphere.errorMessage = ''
                    refractionProperties[side].cylinder.errorMessage = ''

                    if (!rangeFound){
                        refractionProperties[side].sphere.errorMessage = errorMessage
                        refractionProperties[side].cylinder.errorMessage = errorMessage
                        formAndGeometry[side].typeAllowedCode = store.state.moduleOrders.stateCopy.details.formAndGeometry[side].typeAllowedCode
                        formAndGeometry[side].lensDiameterValues = store.state.moduleOrders.stateCopy.details.formAndGeometry[side].lensDiameterValues
                        formAndGeometry[side].type = store.state.moduleOrders.stateCopy.details.formAndGeometry[side].type
                    }
                }
            }
        }
    },
    getters: {
        isFrameAvailable() {
            const basicData = store.state.moduleOrders.basicData
            if (!basicData.selectedOrderType ||
                basicData.selectedOrderType === undefined ||
                !basicData.selectedOrderType.id ||
                basicData.selectedOrderType.id === undefined
            ) return false

            // The frame is available only when the ordertype "Spectacles" is selected
            return basicData.selectedOrderType.id == store.getters.getSpectaclesOrderType.id
        },
        isShapeFromFrameAvailable() {
            const basicData = store.state.moduleOrders.basicData
            if (!basicData.selectedOrderType ||
                basicData.selectedOrderType === undefined ||
                !basicData.selectedOrderType.id ||
                basicData.selectedOrderType.id === undefined
            ) return false

            // The shape is available only for ordertypes:
            // 'Standard lens', 'Remote edging' and 'Frame to come',
            switch (basicData.selectedOrderType.id) {
                case store.getters.getStandardLensOrderType.id: return true;
                case store.getters.getRemoteEdgingOrderType.id: return true;
                case store.getters.getFrameToComeOrderType.id: return true;
            }
            return false
        },
        canContinueToDetails (state, getters, rootState) {
            if (rootState.moduleOrders.inTesting) return true

            const hasLensTypes = store.state.moduleOrders.erp.getLensTypesOnly !== null
            const hasLensFilter = store.state.moduleOrders.erp.getLensFilter !== null
            const hasSalesFrameSupplierList = store.state.moduleOrders.erp.getSalesFramesSupplierList !== null

            return getters.areDraftFieldsEvaluated && hasLensTypes && hasLensFilter && hasSalesFrameSupplierList
        },
        isRemarkEvaluated (state, getters, rootState) {
            // Used in basic data in combination with additional remark seleciton field.
            // Remark text field in basic data is required only when there is an additional remark value selected

            const basicData = rootState.moduleOrders.basicData
            const additionalRemark = basicData.selectedAdditionalRemark
            const remark = basicData.remark


            const hasAdditionalRemark = (additionalRemark && additionalRemark !== undefined && additionalRemark != '')
            const hasRemark = (remark && remark !== undefined && remark != '')

            if (!hasAdditionalRemark) return true
            return hasAdditionalRemark && hasRemark
        },
        isFrameSelected (state, getters, rootState) {
            // Evaluation for when the ordertype is spectacles
            let basicData = rootState.moduleOrders.basicData
            let frame = rootState.moduleOrders.details.frame

            // Evaluation for when the ordertype is spectacles
            let type = basicData.selectedOrderType
            if (type.id != store.getters.getSpectaclesOrderType.id) return true

            if (frame.selectedFrame) return true
            return false
        },
        areDraftFieldsEvaluated (state, getters, rootState) {
            /** Required fields for draft are:
             *  Basic data -> Reference number
            */
            let basicData = rootState.moduleOrders.basicData

            if (basicData.referenceNumber) return true
            return false
        },
        areRequiredFieldsEvaluated (state, getters, rootState) {
            // Get all required fields properties from the state,
            // find values of these fields in the order.js by mapping field names,
            // return any "empty".
            let areRequiredFieldsEvaluated = true
            let requiredFields = getFieldProperties(state, 'required')
            requiredFields.forEach(field => {
                // Field is required
                if (field.value){

                    // Find the original value for this field from orders.js
                    let fieldValue = eval('rootState.moduleOrders.' + field.stateFieldName)

                    // If the required field is null or empty, show an error label message on the field
                    if (fieldValue === undefined || fieldValue === null || fieldValue === '') {
                        areRequiredFieldsEvaluated = false

                        // Error message field label on failed evaluation
                        field.stateField.errorMessage = store.getters.translations.notifications.newValue
                        if (field.stateField.type == 'dropdown')
                            field.stateField.errorMessage = store.getters.translations.notifications.selectValue
                    }
                }
            })
            return areRequiredFieldsEvaluated
        },
        areFieldsInRange (state, getters, rootState) {
            // Assess all fields in range.
            let areFieldsInRange = true
            let rangeFields = getFieldProperties(state, 'inRange')
            rangeFields.forEach(field => {
                // Field is not in range
                if (!field.value){
                    areFieldsInRange = false

                    // Error message field label on failed evaluation
                    field.stateField.errorMessage = store.getters.translations.notifications.outOfDelivery
                }
            })
            return areFieldsInRange
        },
        isLensDiameterGreaterThanMinimum (state, getters, rootState) {
            // If this returns true, it means good; false means bad
            let result = true
            let fields = getFieldProperties(state, 'isLensDiameterGreaterThanMinimum')
            fields.forEach(field => {
                // Lens diameter is greater than the minimum lens diameter
                if (!field.value)
                    result = false
            })
            return result
        },
        isErrorPresent (state, getters, rootState) {
            // Check if any field has any error message present.
            let hasErrors = false
            let errorFields = getFieldProperties(state, 'errorMessage')
            errorFields.forEach(field => {
                // There are some errors present
                if (field.value)
                    hasErrors = true
            })
            return hasErrors
        },
        resetAllErrorMessages(state, getters, rootState) {
            // Helper function to reset all present error messages
            getFieldProperties(state, 'errorMessage').forEach(field => {
                field.stateField.errorMessage = ""
            })
        },
        getAllFieldsWithDisabledFunction(state, getters, rootState) {
            // Helper function to reset all present error messages

            return getFieldProperties(state, 'disabled')
        },
        areIndividualFieldsEvaluated(state, getters, rootState) {
            // Evaluation functionallity is assigned in initializator.js->defineIndividualComponentSideFields
            // when individual fields are defined
            const evaluateDefinedFields = (definedFields) => {
                if (!definedFields || definedFields === undefined || definedFields.length == 0)
                    return true

                return definedFields.every(field => {
                    if (field.evaluation === undefined) return true
                    return field.evaluation(field, state)
                })
            }

            let individual = store.state.moduleOrders.details.individual
            let areDefinedFieldsEvaluated = evaluateDefinedFields(individual.definedFields)
            let areLeftDefinedFieldsEvaluated = evaluateDefinedFields(individual.left.definedFields)
            let areRightDefinedFieldsEvaluated = evaluateDefinedFields(individual.right.definedFields)
            if (areDefinedFieldsEvaluated && areLeftDefinedFieldsEvaluated && areRightDefinedFieldsEvaluated)
                return true
            return false
        }
    }
}

function hasVal (val) {
    return val !== undefined && val !== null && val !== '' && val !== 0 && val !== '0.00'
}

function listHasValues (list){
    return list !== undefined && list && list.length !== undefined && list.length > 0
}

function isComponentEnabled(name) {
    const details = store.state.moduleOrders.details
    return details.isComponentEnabled(name) ? true : false
}

function isComponentEnabledAndHasLens(name, side) {
    const details = store.state.moduleOrders.details
    const hasLens = details.product[side].hasLens

    if (isComponentEnabled(name) && hasLens)
        return true
    return false
}

function isInRangeValidated(val, min, max) {
    if (val === undefined || val === null || val === '') return true

    val = parseFloat(val)
    min = parseFloat(min)
    max = parseFloat(max)
    return Helpers.inRange(val, min, max)
}

function flattenObj(obj, parent, name, res = {}){
    for(let key in obj){
        let propName = parent ? parent + '.' + key : key;
        if(typeof obj[key] == 'object'){
            flattenObj(obj[key], propName, name, res);
        } else {
            if (key == name){
                res[propName] = obj[key];
            }
        }
    }
    return res;
}

function getFieldProperties(state, propertyName) {
    // Go through each state variable. If the variable is a function
    // execute it, if it is a variable, return it.
    // Return an array of objects.
    let allFields = []

    const moduleOrders = store.state.moduleOrders

    // Flatten all details state variables to be like: 'product.right.selectedLensType'
    let fields = flattenObj(state, this, propertyName)
    Object.entries(fields).forEach(field => {

        // Remove the last dot notation object to find the correct object name
        // ie. from details.refraction.left.addition.required -> details.refraction.left.addition
        let stateFieldName = field[0].split('.' + propertyName)[0]
        let stateField = eval('state.' + stateFieldName)

        // If the field has a function, execute it.
        let property = field[1]
        if (typeof field[1] == 'function'){
            property = field[1](stateField)
        }

        // Helper getter and setter to get and set the original object field from the state in order.js
        const getOriginalFieldFunction = () => {
            try {
                return eval('moduleOrders.' + stateFieldName)
            } catch (error) {}
            return null
        }
        const setOriginalFieldFunction = (val) => {
            try {
                eval('moduleOrders.' + stateFieldName + '=' + val)
            } catch (error) {}
        }

        // name: the original trailing name with the property ie. details.refraction.left.addition.required
        // value: the value of the property
        // stateFieldName: trailing name of the object field in the state without the property ie. details.refraction.left.addition
        // stateField: reference to the object field in the state
        // getOriginalField: helper function to get the reference of the original object from orders.js
        // getOriginalField: helper function to set the original object from orders.js
        allFields.push({
            name: field[0],
            value: property,
            stateFieldName: stateFieldName,
            stateField: stateField,
            getOriginalField: getOriginalFieldFunction,
            setOriginalField: setOriginalFieldFunction,
        })
    })
    return allFields
}
