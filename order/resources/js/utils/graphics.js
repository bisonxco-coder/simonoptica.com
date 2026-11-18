import store from '@/store'
import Helpers from '@/utils/helpers.js'
import * as pixiApi from './pixiApi.js'

const self = {
    get iniProperties() { return store.getters.getIniProperties },
    get erp() { return store.state.moduleOrders.erp },
    get productCategory() { return store.state.moduleOrders.details.productCategory },
    get formAndGeometry() { return store.state.moduleOrders.details.formAndGeometry },
    get refraction() { return store.state.moduleOrders.details.refraction },
    get product() { return store.state.moduleOrders.details.product },
    get form() { return store.state.moduleOrders.details.form },
    get calculation() { return store.state.moduleOrders.calculation },
    get individual() { return store.state.moduleOrders.details.individual },
}

var graphicsHeightTimeout = null


var width = 96 * 2 // 2 inches
var dpi = Math.round(width / 2); // dots per inch
var dpimm = dpi * 0.0393700787401575;
export default {
    /**
     * Destroys all the children of the element
     */
    clearDrawingContainer(drawingContainerID) {
        if (!drawingContainerID) return

        try {
            let node = document.getElementById(drawingContainerID)
            while (node.firstChild) {
                node.removeChild(node.firstChild)
            }
        } catch (error) { return }
    },

    /**
     * Draws a basic form
     */
    drawForm(drawingContainerID, graphicsApp = null, formData) {
        if (!formData || formData === undefined) return

        // Create new graphics objects
        try {
            // Create or get Pixi app
            var pixi = graphicsApp
            if (pixi === undefined || pixi === null)
                pixi = pixiApi.createNewPixiApp(drawingContainerID)
            else pixi.reset()
        } catch (error) {
            console.error(error)
            return
        }

        var scale = 25;
        var mathpi = Math.PI / -180;

        var dbl = 18
        var dblScale = (parseInt(dbl) * dpimm) / 2;
        var newDblScale = dblScale / (scale / 100) / 4

        var center_R = 0;
        var center_L = 0;

        var arrX_R = new Array();
        var arrY_R = new Array();
        var arrX_L = new Array();
        var arrY_L = new Array();

        var maxX_R = 0;
        var minX_R = 0;
        var maxY_R = 0;
        var minY_R = 0;

        var maxX_L = 0;
        var minX_L = 0;
        var maxY_L = 0;
        var minY_L = 0;

        var maxX_R_Org = 0;
        var minX_L_Org = 0;

        let arrR = formData.right.rValues
        let arrL = formData.left.rValues

        //erstmal Breite berechen (Rechts)
        for (var i = 0; i < arrR.length; i++) {
            arrX_R[i] = Math.cos(i * mathpi) * (arrR[i] / scale);
            arrY_R[i] = Math.sin(i * mathpi) * (arrR[i] / scale);

            if (arrX_R[i] > maxX_R_Org) {
                maxX_R_Org = arrX_R[i];
            }
        }

        maxX_R = -maxX_R_Org;

        //dann komplett mit DBL zeichen
        const arr_R_polygon = []
        for (var i = 0; i < arrR.length; i++) {
            const x = Math.cos(i * mathpi) * (arrR[i] / scale) - maxX_R_Org - newDblScale;
            const y = Math.sin(i * mathpi) * (arrR[i] / scale);
            arrX_R[i] = x
            arrY_R[i] = y
            arr_R_polygon.push(x, y)

            if (arrX_R[i] > maxX_R) {
                maxX_R = arrX_R[i];
            } else if (arrX_R[i] < minX_R) {
                minX_R = arrX_R[i];
            }

            if (arrY_R[i] > maxY_R) {
                maxY_R = arrY_R[i];
            } else if (arrY_R[i] < minY_R) {
                minY_R = arrY_R[i];
            }
        }

        var center_R = (minX_R - maxX_R) / 2 - newDblScale;

        //erstmal Breite berechen (Links)
        for (var i = 0; i < arrL.length; i++) {
            arrX_L[i] = Math.cos(i * mathpi) * (arrL[i] / scale);
            arrY_L[i] = Math.sin(i * mathpi) * (arrL[i] / scale);

            if (arrX_L[i] < minX_L_Org) {
                minX_L_Org = arrX_L[i];
            }
        }

        minX_L = -minX_L_Org;

        //dann komplett mit DBL zeichen
        const arr_L_polygon = []
        for (var i = 0; i < arrL.length; i++) {
            const x = Math.cos(i * mathpi) * (arrL[i] / scale) - minX_L_Org + newDblScale;
            const y = Math.sin(i * mathpi) * (arrL[i] / scale);
            arrX_L[i] = x
            arrY_L[i] = y
            arr_L_polygon.push(x,y)

            if (arrX_L[i] > maxX_L) {
                maxX_L = arrX_L[i];
            } else if (arrX_L[i] < minX_L) {
                minX_L = arrX_L[i];
            }

            if (arrY_L[i] > maxY_L) {
                maxY_L = arrY_L[i];
            } else if (arrY_L[i] < minY_L) {
                minY_L = arrY_L[i];
            }
        }

        //Form
        pixi.graphics.beginFill(self.iniProperties.lens_graphics_color)
        pixi.graphics.drawPolygon(arr_R_polygon)
        pixi.graphics.drawPolygon(arr_L_polygon)
        pixi.graphics.endFill()

        //Rechts
        if (arrR.length > 0) {
            var center_R = (minX_R - maxX_R) / 2 - newDblScale;

            //Mittelpunkt
            pixi.graphics.lineStyle(1, pixiApi.colors.white);
            pixi.graphics.drawLine(center_R, - 10, center_R, 10);
            pixi.graphics.drawLine(center_R - 10, 0, center_R + 10, 0);

            //VBox
            pixi.graphics.lineStyle(1, pixiApi.colors.darkgreen);
            pixi.graphics.drawLine(- newDblScale, -maxY_R - 20, - newDblScale, -minY_R + 20);
            pixi.graphics.drawLine(minX_R, -maxY_R - 20, minX_R, -minY_R + 20);

            //HBox
            pixi.graphics.drawLine(minX_R - 20, maxY_R, 0, maxY_R);
            pixi.graphics.drawLine(minX_R - 20, minY_R, 0, minY_R);

            //Text
            var hbox_R = Math.round((minX_R - maxX_R) * -1 * scale) / 100;
            pixi.text.drawString(hbox_R.toFixed(1), (minX_R - maxX_R) / 2 - newDblScale - 10, -maxY_R - 20);

            var vbox_R = Math.round((maxY_R + minY_R * -1) * scale) / 100;
            pixi.text.drawString(vbox_R.toFixed(1), minX_R - 50, -10);
        }

        //Links
        if (arrL.length > 0) {
            var center_L = maxX_L / 2 + newDblScale / 2;

            //Mittelpunkt
            pixi.graphics.lineStyle(1, pixiApi.colors.white);
            pixi.graphics.drawLine(center_L, - 10, center_L, 10);
            pixi.graphics.drawLine(center_L - 10, 0, center_L + 10, 0);

            //VBox
            pixi.graphics.lineStyle(1, pixiApi.colors.darkgreen);
            pixi.graphics.drawLine(newDblScale, -maxY_L - 20, newDblScale, -minY_L + 20);
            pixi.graphics.drawLine(maxX_L, -maxY_L - 20, maxX_L, -minY_L + 20);

            //HBox
            pixi.graphics.drawLine(0, maxY_L, maxX_L + 20, maxY_L);
            pixi.graphics.drawLine(0, minY_L, maxX_L + 20, minY_L);

            //Text
            var hbox_L = Math.round((maxX_L + minX_L * -1) * scale) / 100;
            pixi.text.drawString(hbox_L.toFixed(1), center_L - 10, minY_L - 20);
            if (!self.formAndGeometry.left.boxWidth || isNaN(self.formAndGeometry.left.boxWidth))
                self.formAndGeometry.left.boxWidth = hbox_L.toFixed(1)

            var vbox_L = Math.round((maxY_L + minY_L * -1) * scale) / 100;
            pixi.text.drawString(vbox_L.toFixed(1), maxX_L + 10, -10);
        }

        return pixi
    },

    /**
     * Draws a lens preview of the right lens on frame select
     */
    drawFramePreviewLens(drawingContainerID, graphicsApp = null, framePreviewData, scale = 52) {
        if (!drawingContainerID || !framePreviewData || framePreviewData.right === undefined ||
            framePreviewData.right.length == 0 || framePreviewData.right.rValues === undefined ||
            framePreviewData.right.rValues.length == 0) return

        try {
            // Create or get Pixi app
            var pixi = graphicsApp
            if (pixi === undefined || pixi === null)
                pixi = pixiApi.createNewPixiApp(drawingContainerID)
            else pixi.reset()
        } catch (error) {
            console.error(error)
            return
        }

        var scaleSmall = scale;
        var mathpi = Math.PI / -180;

        var arrX_R_Small = new Array();
        var arrY_R_Small = new Array();
        var maxX_R_Small = 0;
        var minX_R_Small = 0;
        var maxY_R_Small = 0;
        var minY_R_Small = 0;

        let arrR = framePreviewData.right.rValues

        //dann komplett mit DBL zeichen
        const arr_R_polygon = []
        for (var i = 0; i < arrR.length; i++) {
            const x = Math.cos(i * mathpi) * (arrR[i] / scaleSmall);
            const y = Math.sin(i * mathpi) * (arrR[i] / scaleSmall);
            arrX_R_Small[i] = x
            arrY_R_Small[i] = y
            arr_R_polygon.push(x,y)

            if (arrX_R_Small[i] > maxX_R_Small) {
                maxX_R_Small = arrX_R_Small[i];
            } else if (arrX_R_Small[i] < minX_R_Small) {
                minX_R_Small = arrX_R_Small[i];
            }

            if (arrY_R_Small[i] > maxY_R_Small) {
                maxY_R_Small = arrY_R_Small[i];
            } else if (arrY_R_Small[i] < minY_R_Small) {
                minY_R_Small = arrY_R_Small[i];
            }
        }

        const lineEndScale = 1

        // kleine Form auf der Order-Seite
        if (arrX_R_Small.length > 0) {
            pixi.graphics.beginFill(self.iniProperties.lens_graphics_color)
            pixi.graphics.drawPolygon(arr_R_polygon)
            pixi.graphics.endFill()

            pixi.graphics.lineStyle(1, pixiApi.colors.white);
            pixi.graphics.drawLine(0, -5 * lineEndScale, 0, 5 * lineEndScale);
            pixi.graphics.drawLine(-5 * lineEndScale, 0, 5 * lineEndScale, 0);

            //VBox
            pixi.graphics.lineStyle(1, pixiApi.colors.darkgreen);
            pixi.graphics.drawLine(maxX_R_Small, -maxY_R_Small - 5 * lineEndScale, maxX_R_Small, -minY_R_Small + 5 * lineEndScale); //R |
            pixi.graphics.drawLine(minX_R_Small, -maxY_R_Small - 5 * lineEndScale, minX_R_Small, -minY_R_Small + 5 * lineEndScale); //L |

            //HBox
            pixi.graphics.drawLine(minX_R_Small - 5 * lineEndScale, maxY_R_Small, maxX_R_Small + 5 * lineEndScale, maxY_R_Small); //B -
            pixi.graphics.drawLine(minX_R_Small - 5 * lineEndScale, minY_R_Small, maxX_R_Small + 5 * lineEndScale, minY_R_Small); //T -

            //Text
            var hbox_R_Small = Math.round((minX_R_Small - maxX_R_Small) * -1 * scaleSmall) / 100;
            pixi.text.drawString(hbox_R_Small.toFixed(1), 0, -maxY_R_Small - 10);

            var vbox_R_Small = Math.round((maxY_R_Small + minY_R_Small * -1) * scaleSmall) / 100;
            pixi.text.drawString(vbox_R_Small.toFixed(1), minX_R_Small - 20, 0);
        }

        return pixi
    },

    /**
     * Draws the lens graphics inside of the Calculation component and assign edge thickness fields in calculation
     */
    drawCalculationLenses(drawingContainerID,  graphicsApp = null, calculationData) {
        const getValOrZero = (obj, prop, zero = 0) => {
            let r = Helpers.getObjProp(obj, prop)
            return r != "" ? parseFloat(r) : zero
        }

        // Return if parameters are empty
        if ((!drawingContainerID || drawingContainerID === undefined || drawingContainerID == '') ||
            (!calculationData || calculationData === undefined)
        ) return


        // Create new graphics objects
        try {
            // Create Pixi app
            var pixi = graphicsApp
            if (pixi === undefined || pixi === null)
                pixi = pixiApi.createNewPixiApp(drawingContainerID)
            else pixi.reset()
        } catch (error) {
            console.error(error)
            return
        }

        // Scale variables
        var im = 96 * 2 // 2 inches
        var dpi = Math.round(im / 2); // dots per inch
        var dpimmCalc = dpi * 0.0393700787401575;
        var scale = 22

        let vcaStrRDraw = Helpers.getObjProp(calculationData, 'vcaStrRDraw')
        let vcaStrLDraw = Helpers.getObjProp(calculationData, 'vcaStrLDraw')
        var arrR = new Array(...vcaStrRDraw);
        if (!vcaStrRDraw || vcaStrRDraw == '')
            var arrR = new Array();

        var arrL = new Array(...vcaStrLDraw);
        if (!vcaStrLDraw || vcaStrLDraw == '')
            var arrL = new Array();


        var errorR = Helpers.getObjProp(calculationData, 'errorR');
        var errorL = Helpers.getObjProp(calculationData, 'errorL');
        const getStringPos = (string, pos, delimiter = ';') => string.split(delimiter)[pos]
        const parseErrorMessageTranslation = (input) => {
            return `(${input})`
        }
        const errorWaningL = getStringPos(parseErrorMessageTranslation(errorL), 3)
        const errorWaningR = getStringPos(parseErrorMessageTranslation(errorR), 3)

        if (arrR.length == 0 && self.product.right.hasLens) {
            var arrRtemp = new Array();
            for (var i = 0; i < 360; i++) {
                var rad = (getValOrZero(calculationData, 'calcDiamR') / 2) * 100;
                arrRtemp.push(rad);
            }
            arrR = arrRtemp;
        }

        if (arrL.length == 0 && self.product.left.hasLens) {
            var arrLtemp = new Array();
            for (var i = 0; i < 360; i++) {
                var rad = (getValOrZero(calculationData, 'calcDiamL') / 2) * 100;
                arrLtemp.push(rad);
            }
            arrL = arrLtemp;
        }

        var maxCalcThicknR = getValOrZero(calculationData, 'maxCalcThicknR') * 100;
        var maxCalcThicknL = getValOrZero(calculationData, 'maxCalcThicknL') * 100;

        var maxCalcThicknAxR = getValOrZero(calculationData, 'maxCalcThicknAxR');
        var maxCalcThicknAxL = getValOrZero(calculationData, 'maxCalcThicknAxL');

        var maxCalcThicknRadR = getValOrZero(calculationData, 'maxCalcThicknRadR');
        var maxCalcThicknRadL = getValOrZero(calculationData, 'maxCalcThicknRadL');

        var minCalcThicknR = getValOrZero(calculationData, 'minCalcThicknR') * 100;
        var minCalcThicknL = getValOrZero(calculationData, 'minCalcThicknL') * 100;

        var minCalcThicknAxR = getValOrZero(calculationData, 'minCalcThicknAxR');
        var minCalcThicknAxL = getValOrZero(calculationData, 'minCalcThicknAxL');

        let thkRArr = Helpers.getObjProp(calculationData, 'thkRArr')
        var arrThkR = new Array(...thkRArr);
        if (!thkRArr || thkRArr == '')
            arrThkR = new Array()

        let thkLArr = Helpers.getObjProp(calculationData, 'thkLArr')
        var arrThkL = new Array(...thkLArr);
        if (!thkLArr || thkLArr == '')
            arrThkL = new Array()

        if (arrThkR.length == 0 && self.product.right.hasLens == true) {
            var arrThkRtemp = new Array();
            for (var i = 0; i < 72; i++) {
                arrThkRtemp.push(maxCalcThicknR);
            }
            arrThkR = arrThkRtemp;
        }

        if (arrThkL.length == 0 && self.product.left.hasLens == true) {
            var arrThkLtemp = new Array();
            for (var i = 0; i < 72; i++) {
                arrThkLtemp.push(maxCalcThicknL);
            }
            arrThkL = arrThkLtemp;
        }

        let diamThkRArr = Helpers.getObjProp(calculationData, 'diamThkRArr')
        let diamThkLArr = Helpers.getObjProp(calculationData, 'diamThkLArr')
        var arrDiamThkR = new Array(...diamThkRArr);
        if (!diamThkRArr || diamThkRArr == '')
            var arrDiamThkR = new Array();
        var arrDiamThkL = new Array(...diamThkLArr);
        if (!diamThkLArr || diamThkLArr == '')
            var arrDiamThkL = new Array();

        if (arrDiamThkR.length == 0 && self.product.right.hasLens == true) {
            var arrDiamThkRtemp = new Array();
            for (var i = 0; i < 72; i++) {
                var rad = (getValOrZero(calculationData, 'calcDiamR') / 2) * 100;
                arrDiamThkRtemp.push(rad);
            }
            arrDiamThkR = arrDiamThkRtemp;
        }

        if (arrDiamThkL.length == 0 && self.product.left.hasLens == true) {
            var arrDiamThkLtemp = new Array();
            for (var i = 0; i < 72; i++) {
                var rad = (getValOrZero(calculationData, 'calcDiamL') / 2) * 100;
                arrDiamThkLtemp.push(rad);
            }
            arrDiamThkL = arrDiamThkLtemp;
        }

        let diamRArr = Helpers.getObjProp(calculationData, 'diamRArr')
        var arrDiamR = new Array(...diamRArr);
        if (!diamRArr || diamRArr == '')
            arrDiamR = new Array()
        let diamLArr = Helpers.getObjProp(calculationData, 'diamLArr')
        var arrDiamL = new Array(...diamLArr);
        if (!diamLArr || diamLArr == '')
            arrDiamL = new Array()
        let degRArr = Helpers.getObjProp(calculationData, 'degRArr')
        var arrDegR = new Array(...degRArr);
        if (!degRArr || degRArr == '')
            arrDegR = new Array()
        let degLArr = Helpers.getObjProp(calculationData, 'degLArr')
        var arrDegL = new Array(...degLArr);
        if (!degLArr || degLArr == '')
            arrDegL = new Array()
        let dist1RArr = Helpers.getObjProp(calculationData, 'dist1RArr')
        var arrDist1R = new Array(...dist1RArr);
        if (!dist1RArr || dist1RArr == '')
            arrDist1R = new Array()
        let dist2RArr = Helpers.getObjProp(calculationData, 'dist2RArr')
        var arrDist2R = new Array(...dist2RArr);
        if (!dist2RArr || dist2RArr == '')
            arrDist2R = new Array()
        let dist1LArr = Helpers.getObjProp(calculationData, 'dist1LArr')
        var arrDist1L = new Array(...dist1LArr);
        if (!dist1LArr || dist1LArr == '')
            arrDist1L = new Array()
        let dist2LArr = Helpers.getObjProp(calculationData, 'dist2LArr')
        var arrDist2L = new Array(...dist2LArr);
        if (!dist2LArr || dist2LArr == '')
            arrDist2L = new Array()


        var ltypR = Helpers.getObjProp(calculationData, 'ltypR');
        var ltypL = Helpers.getObjProp(calculationData, 'ltypL');

        var ltypeR = Helpers.getObjProp(calculationData, 'ltypeR');
        var ltypeL = Helpers.getObjProp(calculationData, 'ltypeL');

        var fcbcinR = getValOrZero(calculationData, 'fcbcinR')
        var fcbcinL = getValOrZero(calculationData, 'fcbcinL')

        var fcbcupR = getValOrZero(calculationData, 'fcbcupR')
        var fcbcupL = getValOrZero(calculationData, 'fcbcupL')

        var fcocinR = getValOrZero(calculationData, 'fcocinR')
        var fcocinL = getValOrZero(calculationData, 'fcocinL')

        var fcocupR = getValOrZero(calculationData, 'fcocupR')
        var fcocupL = getValOrZero(calculationData, 'fcocupL')

        var bcocinR = getValOrZero(calculationData, 'bcocinR')
        var bcocinL = getValOrZero(calculationData, 'bcocinL')

        var bcocupR = getValOrZero(calculationData, 'bcocupR')
        var bcocupL = getValOrZero(calculationData, 'bcocupL')

        // Get corrlen
        var corrLen = 0

        var maxThkR = Math.max.apply(Math, arrThkR);
        var minThkR = Math.min.apply(Math, arrThkR);
        //   var maxDiffDegR = arrDegR[arrDiffR.indexOf(Math.max.apply(Math, arrDiffR))];
        //   var minDiffDegR = arrDegR[arrDiffR.indexOf(Math.min.apply(Math, arrDiffR))];
        var maxThkDegR = arrThkR.indexOf(maxThkR);
        var minThkDegR = arrThkR.indexOf(minThkR);

        var maxThkL = Math.max.apply(Math, arrThkL);
        var minThkL = Math.min.apply(Math, arrThkL);
        //   var maxDiffDegL = arrDegL[arrDiffL.indexOf(Math.max.apply(Math, arrDiffL))];
        //   var minDiffDegL = arrDegL[arrDiffL.indexOf(Math.min.apply(Math, arrDiffL))];
        var maxThkDegL = arrThkL.indexOf(maxThkL);
        var minThkDegL = arrThkL.indexOf(minThkL);

        var status = Helpers.getObjProp(calculationData, 'status');

        var nasalThkR = "";
        var nasalThkL = "";
        var topThkR = "";
        var topThkL = "";
        var tempThkR = "";
        var tempThkL = "";
        var downThkR = "";
        var downThkL = "";

        // Right side
        let lensGeoR = Helpers.getObjProp(self.erp.right.getLensGeo, 'LensGeo')
        if (lensGeoR && lensGeoR !== "") lensGeoR = lensGeoR[0]

        var decX_R = getValOrZero(lensGeoR, 'DECX', 0)
        var decY_R = getValOrZero(lensGeoR, 'DECY', 0)

        var sTypeR = getValOrZero(lensGeoR, 'STYPE', 0)
        var segBoxwidthR = getValOrZero(lensGeoR, 'SEGWIDTH', -1)
        var segBoxheightR = getValOrZero(lensGeoR, 'SEGHEIGHT', -1)
        var segRaduppR = getValOrZero(lensGeoR, 'SEGUPPRAD', -1)
        var segRadlowR = getValOrZero(lensGeoR, 'SEGLOWRAD', -1)
        var PRP_X_R = getValOrZero(lensGeoR, 'PRP_X', 0)
        var PRP_Y_R = getValOrZero(lensGeoR, 'PRP_Y', 0)
        var DRP_X_R = getValOrZero(lensGeoR, 'DRP_X', 0)
        var DRP_Y_R = getValOrZero(lensGeoR, 'DRP_Y', 0)
        var LRP_X_R = getValOrZero(lensGeoR, 'LRP_X', 0)
        var LRP_Y_R = getValOrZero(lensGeoR, 'LRP_Y', 0)
        var NRP_X_R = getValOrZero(lensGeoR, 'NRPMAX_X', 0)
        var NRP_Y_R = getValOrZero(lensGeoR, 'NRPMAX_Y', 0)

        // Left side
        let lensGeoL = Helpers.getObjProp(self.erp.left.getLensGeo, 'LensGeo')
        if (lensGeoL && lensGeoL !== "") lensGeoL = lensGeoL[0]

        var decX_L = getValOrZero(lensGeoL, 'DECX', 0)
        var decY_L = getValOrZero(lensGeoL, 'DECY', 0)

        var sTypeL = getValOrZero(lensGeoL, 'STYPE', 0)
        var segBoxwidthL = getValOrZero(lensGeoL, 'SEGWIDTH', -1)
        var segBoxheightL = getValOrZero(lensGeoL, 'SEGHEIGHT', -1)
        var segRaduppL = getValOrZero(lensGeoL, 'SEGUPPRAD', -1)
        var segRadlowL = getValOrZero(lensGeoL, 'SEGLOWRAD', -1)
        var PRP_X_L = getValOrZero(lensGeoL, 'PRP_X', 0)
        var PRP_Y_L = getValOrZero(lensGeoL, 'PRP_Y', 0)
        var DRP_X_L = getValOrZero(lensGeoL, 'DRP_X', 0)
        var DRP_Y_L = getValOrZero(lensGeoL, 'DRP_Y', 0)
        var LRP_X_L = getValOrZero(lensGeoL, 'LRP_X', 0)
        var LRP_Y_L = getValOrZero(lensGeoL, 'LRP_Y', 0)
        var NRP_X_L = getValOrZero(lensGeoL, 'NRPMAX_X', 0)
        var NRP_Y_L = getValOrZero(lensGeoL, 'NRPMAX_Y', 0)

        if (corrLen > 0) {
            var OCSG_Y_R = 0;
            var OCSG_Y_L = 0;
            if(store.getters.getIniProperties['SETTINGS_CORR_MODE'] === 'PROGLEN'){
                OCSG_Y_R = LRP_Y_R - PRP_Y_R;
                OCSG_Y_L = LRP_Y_L - PRP_Y_L;
            }
            // <?php
            //     if (isset($_SESSION['corrmode']) && $_SESSION['corrmode'] = 'PROGLEN') {
            //         echo "OCSG_Y_R = LRP_Y_R - PRP_Y_R;";
            //         echo "OCSG_Y_L = LRP_Y_L - PRP_Y_L;";
            //     }
            // ?>
            NRP_Y_R = -corrLen + OCSG_Y_R;
            NRP_Y_L = -corrLen + OCSG_Y_L;
        }

        var arrX_R = new Array();
        var arrY_R = new Array();
        var arrX_L = new Array();
        var arrY_L = new Array();

        var arrXThk_R = new Array();
        var arrYThk_R = new Array();
        var arrXThk_L = new Array();
        var arrYThk_L = new Array();

        var arrXCutV11_R = new Array();
        var arrYCutV11_R = new Array();
        var arrXCutV12_R = new Array();
        var arrYCutV12_R = new Array();
        var arrXCutV21_R = new Array();
        var arrYCutV21_R = new Array();
        var arrXCutV22_R = new Array();
        var arrYCutV22_R = new Array();

        var arrXCutV11_L = new Array();
        var arrYCutV11_L = new Array();
        var arrXCutV12_L = new Array();
        var arrYCutV12_L = new Array();
        var arrXCutV21_L = new Array();
        var arrYCutV21_L = new Array();
        var arrXCutV22_L = new Array();
        var arrYCutV22_L = new Array();

        var arrXCutH11_R = new Array();
        var arrYCutH11_R = new Array();
        var arrXCutH12_R = new Array();
        var arrYCutH12_R = new Array();
        var arrXCutH21_R = new Array();
        var arrYCutH21_R = new Array();
        var arrXCutH22_R = new Array();
        var arrYCutH22_R = new Array();

        var arrXCutH11_L = new Array();
        var arrYCutH11_L = new Array();
        var arrXCutH12_L = new Array();
        var arrYCutH12_L = new Array();
        var arrXCutH21_L = new Array();
        var arrYCutH21_L = new Array();
        var arrXCutH22_L = new Array();
        var arrYCutH22_L = new Array();

        var maxX_R = 0;
        var minX_R = 0;
        var maxY_R = 0;
        var minY_R = 0;
        var maxX_L = 0;
        var minX_L = 0;
        var maxY_L = 0;
        var minY_L = 0;
        var maxX_R_Org = 0;
        var minX_L_Org = 0;
        var minThkRStr = "0.0";
        var maxThkRStr = "0.0";
        var minThkLStr = "0.0";
        var maxThkLStr = "0.0";

        // Set DBL
        var dbl = self.formAndGeometry.dbl;
        if (!dbl || dbl === undefined || dbl == "") {
            dbl = 18.0;
            self.formAndGeometry.dbl = dbl
        }
        var dblScale = (parseInt(dbl) * dpimmCalc) / 2;

        //erstmal Breite berechen (Rechts)
        for (var i = 0; i < arrR.length; i++) {
            arrX_R[i] = Math.cos(i * (Math.PI / -180)) * (arrR[i] / scale);
            arrY_R[i] = Math.sin(i * (Math.PI / -180)) * (arrR[i] / scale);

            if (arrX_R[i] > maxX_R_Org) {
                maxX_R_Org = arrX_R[i];
            }
        }
        maxX_R = -maxX_R_Org;

        //dann komplett mit DBL zeichen
        for (var i = 0; i < arrR.length; i++) {
            arrX_R[i] = Math.cos(i * (Math.PI / -180)) * (arrR[i] / scale) - maxX_R_Org - dblScale;
            arrY_R[i] = Math.sin(i * (Math.PI / -180)) * (arrR[i] / scale);

            if (arrX_R[i] > maxX_R) {
                maxX_R = arrX_R[i];
            } else if (arrX_R[i] < minX_R) {
                minX_R = arrX_R[i];
            }

            if (arrY_R[i] > maxY_R) {
                maxY_R = arrY_R[i];
            } else if (arrY_R[i] < minY_R) {
                minY_R = arrY_R[i];
            }
        }

        var topDiamR = 0;
        var downDiamR = 0;
        var nasalDiamR = 0;
        var tempDiamR = 0;
        //var maxDiffR = 0;
        var arrXmaxThkR = 0;
        var arrYmaxThkR = 0;
        //var minDiffR = 1000;
        var arrXminThkR = 0;
        var arrYminThkR = 0;

        //Dicke zeichnen
        var k = 0;
        if (arrDiamThkR.length > 0 && (errorR === "" || errorWaningR.charAt(0) === 'W')) {
            var j = 0;
            for (var i = 0; i < arrR.length; i++) {
                if (i % 5 == 0) {
                    arrXThk_R[k] = Math.cos(i * (Math.PI / -180)) * ((arrR[i] + arrThkR[j]) / scale) - maxX_R_Org - dblScale;
                    arrYThk_R[k] = Math.sin(i * (Math.PI / -180)) * ((arrR[i] + arrThkR[j]) / scale);
                    // found = 1;
                    k++;
                    if (i == 0) {
                        nasalThkR = arrThkR[j];
                        nasalDiamR = arrDiamThkR[j];
                    }
                    if (i == 90) {
                        topThkR = arrThkR[j];
                        topDiamR = arrDiamThkR[j];
                    }
                    if (i == 180) {
                        tempThkR = arrThkR[j];
                        tempDiamR = arrDiamThkR[j];
                    }
                    if (i == 270) {
                        downThkR = arrThkR[j];
                        downDiamR = arrDiamThkR[j];
                    }
                    ++j;
                }
                if (i == maxCalcThicknAxR) { //maxThkDegR
                    //Dicksten Punkt berechnen
                    arrXmaxThkR = Math.cos(i * (Math.PI / -180)) * ((arrR[i]) / scale) - maxX_R_Org - dblScale;
                    arrYmaxThkR = Math.sin(i * (Math.PI / -180)) * ((arrR[i]) / scale);
                }
                if (i == minCalcThicknAxR) { //minThkDegR
                    //Dünnsten Punkt berechnen
                    arrXminThkR = Math.cos(i * (Math.PI / -180)) * ((arrR[i]) / scale) - maxX_R_Org - dblScale;
                    arrYminThkR = Math.sin(i * (Math.PI / -180)) * ((arrR[i]) / scale);
                }
            }
        }

        //Schnitt berechnen
        //      var k = 0;
        //      var l = 0;
        //      var m = 0;
        //      var n = 0;
        //      for (var i = 0; i < arrDegR.length; i++){
        //         if (parseInt(arrDegR[i]) == 90 && topDiamR > arrDiamR[i]) {
        //            arrXCutV11_R[k] = (arrDist1R[i]/scale) - maxX_R_Org - dblScale - (tempDiamR)/scale - 50;
        //            arrYCutV11_R[k] = -(arrDiamR[i]);
        //            arrXCutV12_R[k] = (arrDist2R[i]/scale) - maxX_R_Org - dblScale - (tempDiamR)/scale - 50;
        //            arrYCutV12_R[k] = -(arrDiamR[i]);
        //            k++;
        //         }
        //
        //         if (parseInt(arrDegR[i]) == 270 && downDiamR > arrDiamR[i]) {
        //            arrXCutV21_R[l] = (arrDist1R[i]/scale) - maxX_R_Org - dblScale - (tempDiamR)/scale - 50;
        //            arrYCutV21_R[l] = arrDiamR[i];
        //            arrXCutV22_R[l] = (arrDist2R[i]/scale) - maxX_R_Org - dblScale - (tempDiamR)/scale - 50;
        //            arrYCutV22_R[l] = arrDiamR[i];
        //            l++;
        //         }
        //
        //         if (parseInt(arrDegR[i]) == 0 && nasalDiamR > arrDiamR[i]) {
        //            arrXCutH11_R[m] = arrDiamR[i] - maxX_R_Org - dblScale;
        //            arrYCutH11_R[m] = (arrDist1R[i]/scale)*-1 - minY_R + 50;
        //            arrXCutH12_R[m] = arrDiamR[i] - maxX_R_Org - dblScale;
        //            arrYCutH12_R[m] = (arrDist2R[i]/scale)*-1 - minY_R + 50;
        //            m++;
        //         }
        //
        //         if (parseInt(arrDegR[i]) == 180 && tempDiamR > arrDiamR[i]) {
        //            arrXCutH21_R[n] = -arrDiamR[i] - maxX_R_Org - dblScale;
        //            arrYCutH21_R[n] = (arrDist1R[i]/scale)*-1 - minY_R + 50;
        //            arrXCutH22_R[n] = -arrDiamR[i] - maxX_R_Org - dblScale;
        //            arrYCutH22_R[n] = (arrDist2R[i]/scale)*-1 - minY_R + 50;
        //            n++;
        //         }
        //      }

        //erstmal Breite berechen (Links)
        for (var i = 0; i < arrL.length; i++) {
            arrX_L[i] = Math.cos(i * (Math.PI / -180)) * (arrL[i] / scale);
            arrY_L[i] = Math.sin(i * (Math.PI / -180)) * (arrL[i] / scale);

            if (arrX_L[i] < minX_L_Org) {
                minX_L_Org = arrX_L[i];
            }
        }
        minX_L = -minX_L_Org;

        //dann komplett mit DBL zeichen
        for (var i = 0; i < arrL.length; i++) {
            arrX_L[i] = Math.cos(i * (Math.PI / -180)) * (arrL[i] / scale) - minX_L_Org + dblScale;
            arrY_L[i] = Math.sin(i * (Math.PI / -180)) * (arrL[i] / scale);

            if (arrX_L[i] > maxX_L) {
                maxX_L = arrX_L[i];
            } else if (arrX_L[i] < minX_L) {
                minX_L = arrX_L[i];
            }

            if (arrY_L[i] > maxY_L) {
                maxY_L = arrY_L[i];
            } else if (arrY_L[i] < minY_L) {
                minY_L = arrY_L[i];
            }
        }

        var topDiamL = 0;
        var downDiamL = 0;
        var nasalDiamL = 0;
        var tempDiamL = 0;
        //var maxDiffL = 0;
        var arrXmaxThkL = 0;
        var arrYmaxThkL = 0;
        //var minDiffL = 1000;
        var arrXminThkL = 0;
        var arrYminThkL = 0;

        //Dicke zeichnen
        var k = 0;

        if (arrDiamThkL.length > 0 && (errorL === "" || errorWaningL.charAt(0) === 'W')) {
            var j = 0;
            for (var i = 0; i < arrL.length; i++) {
                if (i % 5 == 0) {
                    arrXThk_L[k] = Math.cos(i * (Math.PI / -180)) * ((arrL[i] + arrThkL[j]) / scale) - minX_L_Org + dblScale;
                    arrYThk_L[k] = Math.sin(i * (Math.PI / -180)) * ((arrL[i] + arrThkL[j]) / scale);
                    // found = 1;
                    k++;
                    if (i == 180) {
                        nasalThkL = arrThkL[j];
                        nasalDiamL = arrDiamThkL[j];
                    }
                    if (i == 90) {
                        topThkL = arrThkL[j];
                        topDiamL = arrDiamThkL[j];
                    }
                    if (i == 0) {
                        tempThkL = arrThkL[j];
                        tempDiamL = arrDiamThkL[j];
                    }
                    if (i == 270) {
                        downThkL = arrThkL[j];
                        downDiamL = arrDiamThkL[j];
                    }
                    ++j;
                }
                if (i == maxCalcThicknAxL) { //maxThkDegL
                    //Dickesten Punkt berechnen
                    arrXmaxThkL = Math.cos(i * (Math.PI / -180)) * (arrL[i] / scale) - minX_L_Org + dblScale;
                    arrYmaxThkL = Math.sin(i * (Math.PI / -180)) * (arrL[i] / scale);
                }
                if (i == minCalcThicknAxL) { //minThkDegL
                    //Dünnsten Punkt berechnen
                    arrXminThkL = Math.cos(i * (Math.PI / -180)) * (arrL[i] / scale) - minX_L_Org + dblScale;
                    arrYminThkL = Math.sin(i * (Math.PI / -180)) * (arrL[i] / scale);
                }
            }
        }

        //Schnitt berechnen
        //      var k = 0;
        //      var l = 0;
        //      var m = 0;
        //      var n = 0;
        //      for (var i=0; i<arrDegL.length; i++){
        //         if (parseInt(arrDegL[i]) == 90 && topDiamL > arrDiamL[i]) {
        //            arrXCutV11_L[k] = (arrDist1L[i]/scale)*-1 - minX_L_Org + dblScale + 180;
        //            arrYCutV11_L[k] = -(arrDiamL[i]/(scale/100))/2;
        //            arrXCutV12_L[k] = (arrDist2L[i]/scale)*-1 - minX_L_Org + dblScale + 180;
        //            arrYCutV12_L[k] = -(arrDiamL[i]/(scale/100))/2;
        //            k++;
        //         }
        //
        //         if (parseInt(arrDegL[i]) == 270 && downDiamL > arrDiamL[i]) {
        //            arrXCutV21_L[l] = (arrDist1L[i]/scale)*-1 - minX_L_Org + dblScale + 180;
        //            arrYCutV21_L[l] = (arrDiamL[i]/(scale/100))/2;
        //            arrXCutV22_L[l] = (arrDist2L[i]/scale)*-1 - minX_L_Org + dblScale + 180;
        //            arrYCutV22_L[l] = (arrDiamL[i]/(scale/100))/2;
        //            l++;
        //         }
        //
        //         if (parseInt(arrDegL[i]) == 0 && nasalDiamL > arrDiamL[i]) {
        //            arrXCutH11_L[m] = -(arrDiamL[i]/(scale/100))/2 - minX_L_Org + dblScale;
        //            arrYCutH11_L[m] = (arrDist1L[i]/scale)*-1 - minY_L + 50;
        //            arrXCutH12_L[m] = -(arrDiamL[i]/(scale/100))/2 - minX_L_Org + dblScale;
        //            arrYCutH12_L[m] = (arrDist2L[i]/scale)*-1 - minY_L + 50;
        //            m++;
        //         }
        //
        //         if (parseInt(arrDegL[i]) == 180 && tempDiamL > arrDiamL[i]) {
        //            arrXCutH21_L[n] = (arrDiamL[i]/(scale/100))/2 - minX_L_Org + dblScale;
        //            arrYCutH21_L[n] = (arrDist1L[i]/scale)*-1 - minY_L + 50;
        //            arrXCutH22_L[n] = (arrDiamL[i]/(scale/100))/2 - minX_L_Org + dblScale;
        //            arrYCutH22_L[n] = (arrDist2L[i]/scale)*-1 - minY_L + 50;
        //            n++;
        //         }
        //      }

        var center_R = (minX_R - maxX_R) / 2 - dblScale;
        var center_L = maxX_L / 2 + dblScale / 2;

        //Rechts
        if (arrDiamThkR.length > 0 && (errorR === "" || errorWaningR.charAt(0) === 'W')) {
            if (arrR.length > 0 && status == true) {
                pixi.graphics.lineStyle(1, pixiApi.colors.red);
                const arrThk_R = []
                for (var i = 0; i < arrXThk_R.length; i++) {
                    pixi.graphics.drawCrispLine(0 + center_R, 0, arrXThk_R[i], arrYThk_R[i])
                    arrThk_R.push(arrXThk_R[i], arrYThk_R[i])
                }

                //Dicke
                pixi.graphics.drawPolygon(arrThk_R)

                //Form
                const arr_R = []
                for (let i = 0; i < arrX_R.length; i++) {
                    arr_R.push(arrX_R[i], arrY_R[i])
                }
                // pixi.graphics.beginFill(helpers.colorNameToHex(self.iniProperties.lens_graphics_color))
                pixi.graphics.beginFill(self.iniProperties.lens_graphics_color)
                pixi.graphics.lineStyle(0, pixiApi.colors.red);
                pixi.graphics.drawPolygon(arr_R)
                pixi.graphics.endFill()

                //Design
                if (ltypeR == "SV" && ltypR != "BI") {
                    //optische Zentrum
                    pixi.graphics.beginFill(pixiApi.colors.yellow)
                    pixi.graphics.drawEllipse(center_R + fcocinR / (scale / 100), -fcocupR / (scale / 100), 2, 2);
                } else if (ltypeR == "PR") {
                    var centerX = center_R + (fcocinR - bcocinR + DRP_X_R) / (scale / 100)
                    var centerY = - (fcocupR - bcocupR + DRP_Y_R) / (scale / 100)
                    var radius = 25
                    var startAngle = -((300 / 180) * Math.PI)
                    var endAngle = -((240 / 180) * Math.PI)

                    pixi.graphics.lineStyle(1, pixiApi.colors.yellow);
                    pixi.graphics.arc(centerX, centerY, radius, startAngle, endAngle, true);

                    //Fernbezugspunkt
                    // ctx.beginPath();
                    // ctx.arc(360 + center_R + (fcocinR - bcocinR + DRP_X_R) / (scale / 100), 146 - (fcocupR - bcocupR + DRP_Y_R) / (scale / 100), 25, -((300 / 180) * Math.PI), -((240 / 180) * Math.PI), true);
                    // //            ctx.arc(361 + center_R + (fcbcinR + DRP_X_R)/(scale/100), 140 - (fcbcupR + DRP_Y_R)/(scale/100) , 25,-((300/180)*Math.PI),-((240/180)*Math.PI), true);
                    // ctx.strokeStyle = 'yellow';
                    // ctx.stroke();
                    //Segmentbezugspunkt
                    pixi.graphics.drawCrispLine(center_R + (fcocinR - bcocinR + LRP_X_R) / (scale / 100), -(fcocupR - bcocupR + LRP_Y_R) / (scale / 100) - 5, center_R + (fcocinR - bcocinR + LRP_X_R) / (scale / 100), -(fcocupR - bcocupR + LRP_Y_R) / (scale / 100) + 5);
                    pixi.graphics.drawCrispLine(center_R + (fcocinR - bcocinR + LRP_X_R) / (scale / 100) - 5, -(fcocupR - bcocupR + LRP_Y_R) / (scale / 100), center_R + (fcocinR - bcocinR + LRP_X_R) / (scale / 100) + 5, -(fcocupR - bcocupR + LRP_Y_R) / (scale / 100));
                    //            jgCalc.drawLine(center_R + (fcbcinR + LRP_X_R)/(scale/100)    , -(fcbcupR + LRP_Y_R)/(scale/100) - 5, center_R + (fcbcinR + LRP_X_R)/(scale/100)    , -(fcbcupR + LRP_Y_R)/(scale/100) + 5);
                    //            jgCalc.drawLine(center_R + (fcbcinR + LRP_X_R)/(scale/100) - 5, -(fcbcupR + LRP_Y_R)/(scale/100)    , center_R + (fcbcinR + LRP_X_R)/(scale/100) + 5, -(fcbcupR + LRP_Y_R)/(scale/100)    );
                    //optische Zentrum
                    pixi.graphics.beginFill(pixiApi.colors.yellow)
                    pixi.graphics.drawEllipse(center_R + fcocinR / (scale / 100), -fcocupR / (scale / 100), 2, 2);
                    //Nahbezugspunkt
                    pixi.graphics.drawEllipse(center_R + (fcocinR - bcocinR + NRP_X_R) / (scale / 100), -(fcocupR - bcocupR + NRP_Y_R) / (scale / 100), 10, 10);
                    //Funktionsgravurpunkte
                    //rechts
                    pixi.graphics.drawEllipse(center_R + (fcocinR + 17) / (scale / 100), -fcocupR / (scale / 100), 4, 4);
                    pixi.graphics.drawCrispLine(center_R + (fcocinR + 12) / (scale / 100), -fcocupR / (scale / 100), center_R + (fcocinR + 15) / (scale / 100) + 2, -fcocupR / (scale / 100));
                    pixi.graphics.drawCrispLine(center_R + (fcocinR + 19) / (scale / 100) - 2, -fcocupR / (scale / 100), center_R + (fcocinR + 22) / (scale / 100), -fcocupR / (scale / 100));
                    //links
                    pixi.graphics.drawEllipse(center_R + (fcocinR - 17) / (scale / 100), -fcocupR / (scale / 100), 4, 4);
                    pixi.graphics.drawCrispLine(center_R + (fcocinR - 12) / (scale / 100), -fcocupR / (scale / 100), center_R + (fcocinR - 15) / (scale / 100) - 2, -fcocupR / (scale / 100));
                    pixi.graphics.drawCrispLine(center_R + (fcocinR - 19) / (scale / 100) + 2, -fcocupR / (scale / 100), center_R + (fcocinR - 22) / (scale / 100), -fcocupR / (scale / 100));
                } else if (ltypR == "BI") {
                    var decXScale = (fcbcinR + LRP_X_R) / (scale / 100);
                    var decYScale = (fcbcupR + LRP_Y_R) / (scale / 100);
                    //optische Zentrum
                    pixi.graphics.beginFill(pixiApi.colors.yellow)
                    pixi.graphics.drawEllipse(center_R + fcocinR / (scale / 100), -fcocupR / (scale / 100), 2, 2);

                    if (sTypeR == "0") {                        // Curved Segment
                        var C = segRaduppR + segRadlowR - segBoxheightR;
                        var S = (segRaduppR + segRadlowR + C) / 2;
                        var R = Math.sqrt(((S - segRadlowR) * (S - segRaduppR) * (S - C)) / S);

                        var DeltaAlpha = (2 * Math.atan(R / (S - segRadlowR))) * (180 / Math.PI);
                        var DeltaBeta = (2 * Math.atan(R / (S - segRaduppR))) * (180 / Math.PI);

                        var StartAlpha = Math.round(90 - DeltaAlpha, 0);
                        var StartBeta = Math.round(270 - DeltaBeta, 0);

                        DeltaAlpha = Math.round(2 * DeltaAlpha, 0);
                        DeltaBeta = Math.round(2 * DeltaBeta, 0);

                        //oben
                        pixi.graphics.lineStyle(1, pixiApi.colors.yellow);
                        pixi.graphics.arc(center_R + decXScale, decYScale + segRaduppR / (scale / 100), segRaduppR / (scale / 100), -((StartAlpha / 180) * Math.PI), -(((StartAlpha + DeltaAlpha) / 180) * Math.PI), true);
                        //unten
                        pixi.graphics.arc(center_R + decXScale, decYScale + segRadlowR / (scale / 100) - (segBoxheightR / 2) / (scale / 100), segRadlowR / (scale / 100), -((StartBeta / 180) * Math.PI), -(((StartBeta + DeltaBeta) / 180) * Math.PI), true);
                    } //else if (sTypeR == "1") {                         // Straight segment
                    //}

                    //T
                    pixi.graphics.lineStyle(1, pixiApi.colors.yellow);
                    pixi.graphics.drawCrispLine(center_R + decXScale, -decYScale + 5, center_R + decXScale, -decYScale);
                    pixi.graphics.drawCrispLine(center_R + decXScale - 5, -decYScale, center_R + decXScale + 5, -decYScale);
                }
                //Mittelpunkt
                pixi.graphics.lineStyle(1, pixiApi.colors.white);
                pixi.graphics.drawCrispLine(center_R, - 10, center_R, 10);
                pixi.graphics.drawCrispLine(center_R - 10, 0, center_R + 10, 0);

                //Schnitt
                //         jgCalc.setColor("green");
                //         jgCalc.drawPolyline(arrXCutV11_R, arrYCutV11_R);
                //         jgCalc.drawPolyline(arrXCutV12_R, arrYCutV12_R);
                //         jgCalc.drawLine(arrXCutV11_R[0], 0, arrXCutV11_R[0], arrYCutV11_R[0]);
                //         jgCalc.drawLine(arrXCutV12_R[0], 0, arrXCutV12_R[0], arrYCutV12_R[0]);
                //         jgCalc.drawLine(arrXCutV11_R[arrXCutV11_R.length-1], arrYCutV11_R[arrYCutV11_R.length-1], arrXCutV12_R[arrXCutV12_R.length-1], arrYCutV12_R[arrYCutV12_R.length-1]);
                //         jgCalc.drawString(Math.round(topThkR/100*10)/10, arrXCutV11_R[arrXCutV11_R.length-1] - 10, arrYCutV11_R[arrYCutV11_R.length-1] - 20);
                //
                //         jgCalc.drawPolyline(arrXCutV21_R, arrYCutV21_R);
                //         jgCalc.drawPolyline(arrXCutV22_R, arrYCutV22_R);
                //         jgCalc.drawLine(arrXCutV21_R[0], 0, arrXCutV21_R[0], arrYCutV21_R[0]);
                //         jgCalc.drawLine(arrXCutV22_R[0], 0, arrXCutV22_R[0], arrYCutV22_R[0]);
                //         jgCalc.drawLine(arrXCutV21_R[arrXCutV21_R.length-1], arrYCutV21_R[arrYCutV21_R.length-1], arrXCutV22_R[arrXCutV12_R.length-1], arrYCutV22_R[arrYCutV22_R.length-1]);
                //         jgCalc.drawString(Math.round(downThkR/100*10)/10, arrXCutV21_R[arrXCutV21_R.length-1] - 10, arrYCutV21_R[arrYCutV21_R.length-1]);
                //
                //         jgCalc.drawPolyline(arrXCutH11_R, arrYCutH11_R);
                //         jgCalc.drawPolyline(arrXCutH12_R, arrYCutH12_R);
                //         jgCalc.drawLine(arrXCutH11_R[0], arrYCutH11_R[0], center_R, arrYCutH11_R[0]);
                //         jgCalc.drawLine(arrXCutH12_R[0], arrYCutH12_R[0], center_R, arrYCutH12_R[0]);
                //         jgCalc.drawLine(arrXCutH11_R[arrXCutH11_R.length-1], arrYCutH11_R[arrYCutH11_R.length-1], arrXCutH12_R[arrXCutH12_R.length-1], arrYCutH12_R[arrYCutH12_R.length-1]);
                //         jgCalc.drawString(Math.round(nasalThkR/100*10)/10, arrXCutH11_R[arrXCutH11_R.length-1] - 10, arrYCutH11_R[arrYCutH11_R.length-1]);
                //
                //         jgCalc.drawPolyline(arrXCutH21_R, arrYCutH21_R);
                //         jgCalc.drawPolyline(arrXCutH22_R, arrYCutH22_R);
                //         jgCalc.drawLine(arrXCutH21_R[0], arrYCutH21_R[0], center_R, arrYCutH21_R[0]);
                //         jgCalc.drawLine(arrXCutH22_R[0], arrYCutH22_R[0], center_R, arrYCutH22_R[0]);
                //         jgCalc.drawLine(arrXCutH21_R[arrXCutH21_R.length-1], arrYCutH21_R[arrYCutH21_R.length-1], arrXCutH22_R[arrXCutH22_R.length-1], arrYCutH22_R[arrYCutH22_R.length-1]);
                //         jgCalc.drawString(Math.round(tempThkR/100*10)/10, arrXCutH21_R[arrXCutH21_R.length-1] - 10, arrYCutH21_R[arrYCutH21_R.length-1]);

                //dickesten Punkt
                pixi.graphics.lineStyle(0, pixiApi.colors.white);
                pixi.graphics.beginFill(pixiApi.colors.red)
                pixi.graphics.drawEllipse(arrXmaxThkR, arrYmaxThkR, 4, 4);
                var diffX = 0;
                var diffY = 0;
                if (arrXmaxThkR < center_R) {
                    diffX = -10;
                } else {
                    diffX = 10;
                }
                if (arrYmaxThkR < 0) {
                    diffY = -20;
                } else {
                    diffY = 10;
                }
                maxThkRStr = (Math.round(parseInt(maxCalcThicknR) / 100 * 10) / 10).toFixed(1); //maxThkR
                if (maxThkRStr == "-Infinity") {
                    maxThkRStr = "0";
                }
                pixi.text.drawString(maxThkRStr, arrXmaxThkR + diffX, arrYmaxThkR + diffY, pixiApi.colors.red);

                //dünnsten Punkt
                pixi.graphics.beginFill(pixiApi.colors.red)
                pixi.graphics.drawEllipse(arrXminThkR, arrYminThkR, 3, 3);
                if (arrXminThkR < center_R) {
                    diffX = 10;
                } else {
                    diffX = 0;
                }
                if (arrYminThkR < 0) {
                    diffY = -15;
                } else {
                    diffY = 10;
                }
                minThkRStr = (Math.round(parseInt(minCalcThicknR) / 100 * 10) / 10).toFixed(1); //minThkR
                if (minThkRStr == "Infinity") {
                    minThkRStr = "0";
                }
                pixi.text.drawString(minThkRStr, arrXminThkR + diffX, arrYminThkR + diffY, pixiApi.colors.red);
            }
        }

        //Links
        if (arrDiamThkL.length > 0 && (errorL === "" || errorWaningL.charAt(0) === 'W')) {
            if (arrL.length > 0 && status == true) {
                pixi.graphics.lineStyle(1, pixiApi.colors.red);
                const arrThk_L = []
                for (var i = 0; i < arrXThk_L.length; i++) {
                    pixi.graphics.drawCrispLine(0 + center_L, 0, arrXThk_L[i], arrYThk_L[i]);
                    arrThk_L.push(arrXThk_L[i], arrYThk_L[i])
                }
                //Dicke
                pixi.graphics.drawPolygon(arrThk_L);

                //Form
                const arr_L = []
                for (let i = 0; i < arrX_L.length; i++) {
                    arr_L.push(arrX_L[i], arrY_L[i])
                }
                // pixi.graphics.beginFill(helpers.colorNameToHex(self.iniProperties.lens_graphics_color))
                pixi.graphics.beginFill(self.iniProperties.lens_graphics_color)
                pixi.graphics.lineStyle(0, pixiApi.colors.red);
                pixi.graphics.drawPolygon(arr_L)
                pixi.graphics.endFill()

                //Mittelpunkt
                pixi.graphics.lineStyle(1, pixiApi.colors.white);
                pixi.graphics.drawCrispLine(center_L, - 10, center_L, 10);
                pixi.graphics.drawCrispLine(center_L - 10, 0, center_L + 10, 0);
                pixi.graphics.endFill()

                //Design
                if (ltypeL == "SV" && ltypL != "BI") {
                    //optische Zentrum
                    pixi.graphics.beginFill(pixiApi.colors.yellow)
                    pixi.graphics.drawEllipse(center_L - fcocinL / (scale / 100),- fcocupL / (scale / 100), 2, 2);
                } else if (ltypeL == "PR") {
                    var centerX = center_L - (fcocinL - bcocinL - DRP_X_L) / (scale / 100)
                    var centerY = - (fcocupL - bcocupL + DRP_Y_L) / (scale / 100)
                    var radius = 25
                    var startAngle = -((300 / 180) * Math.PI)
                    var endAngle = -((240 / 180) * Math.PI)

                    pixi.graphics.lineStyle(1, pixiApi.colors.yellow);
                    pixi.graphics.arc(centerX, centerY, radius, startAngle, endAngle, true);

                    //Fernbezugspunkt
                    // ctx.beginPath();
                    // ctx.arc(360 + center_L - (fcocinL - bcocinL - DRP_X_L) / (scale / 100), 146 - (fcocupL - bcocupL + DRP_Y_L) / (scale / 100), 25, -((300 / 180) * Math.PI), -((240 / 180) * Math.PI), true);
                    // //ctx.arc(361 + center_L - (fcbcinL + DRP_X_L)/(scale/100), 140 - (fcbcupL + DRP_Y_L)/(scale/100) , 25,-((300/180)*Math.PI),-((240/180)*Math.PI), true);
                    // ctx.strokeStyle = 'yellow';
                    // ctx.stroke();
                    //Segmentbezugspunkt
                    pixi.graphics.drawCrispLine(center_L - (fcocinL - bcocinL - LRP_X_L) / (scale / 100), -(fcocupL - bcocupL + LRP_Y_L) / (scale / 100) - 5, center_L - (fcocinL - bcocinL - LRP_X_L) / (scale / 100), -(fcocupL - bcocupL + LRP_Y_L) / (scale / 100) + 5);
                    pixi.graphics.drawCrispLine(center_L - (fcocinL - bcocinL - LRP_X_L) / (scale / 100) - 5, -(fcocupL - bcocupL + LRP_Y_L) / (scale / 100), center_L - (fcocinL - bcocinL - LRP_X_L) / (scale / 100) + 5, -(fcocupL - bcocupL + LRP_Y_L) / (scale / 100));
                    //optische Zentrum
                    pixi.graphics.beginFill(pixiApi.colors.yellow)
                    pixi.graphics.drawEllipse(center_L - fcocinL / (scale / 100), -fcocupL / (scale / 100), 2, 2);
                    //Nahbezugspunkt
                    pixi.graphics.drawEllipse(center_L - (fcocinL - bcocinL - NRP_X_L) / (scale / 100), -(fcocupL - bcocupL + NRP_Y_L) / (scale / 100), 10, 10);
                    //Funktionsgravurpunkte
                    //rechts
                    pixi.graphics.drawEllipse(center_L - (fcocinL - 17) / (scale / 100), -fcocupL / (scale / 100), 4, 4);
                    pixi.graphics.drawCrispLine(center_L - (fcocinL - 12) / (scale / 100), -fcocupL / (scale / 100), center_L - (fcocinL - 16) / (scale / 100) - 2, -fcocupL / (scale / 100));
                    pixi.graphics.drawCrispLine(center_L - (fcocinL - 18) / (scale / 100) + 2, -fcocupL / (scale / 100), center_L - (fcocinL - 22) / (scale / 100), -fcocupL / (scale / 100));
                    //links
                    pixi.graphics.drawEllipse(center_L - (fcocinL + 17) / (scale / 100), -fcocupL / (scale / 100), 4, 4);
                    pixi.graphics.drawCrispLine(center_L - (fcocinL + 12) / (scale / 100), -fcocupL / (scale / 100), center_L - (fcocinL + 16) / (scale / 100) + 2, -fcocupL / (scale / 100));
                    pixi.graphics.drawCrispLine(center_L - (fcocinL + 18) / (scale / 100) - 2, -fcocupL / (scale / 100), center_L - (fcocinL + 22) / (scale / 100), -fcocupL / (scale / 100));
                } else if (ltypL == "BI") {
                    var decXScale = (-fcbcinL + LRP_X_L) / (scale / 100);
                    var decYScale = (fcbcupL + LRP_Y_L) / (scale / 100);
                    //optische Zentrum
                    pixi.graphics.beginFill(pixiApi.colors.yellow)
                    pixi.graphics.drawEllipse(center_L - fcocinL / (scale / 100), -fcocupL / (scale / 100), 2, 2);

                    if (sTypeR == "0") {                        // Curved Segment
                        var C = segRaduppL + segRadlowL - segBoxheightL;
                        var S = (segRaduppL + segRadlowL + C) / 2;
                        var R = Math.sqrt(((S - segRadlowL) * (S - segRaduppL) * (S - C)) / S);

                        var DeltaAlpha = (2 * Math.atan(R / (S - segRadlowL))) * (180 / Math.PI);
                        var DeltaBeta = (2 * Math.atan(R / (S - segRaduppL))) * (180 / Math.PI);

                        var StartAlpha = Math.round(90 - DeltaAlpha, 0);
                        var StartBeta = Math.round(270 - DeltaBeta, 0);

                        DeltaAlpha = Math.round(2 * DeltaAlpha, 0);
                        DeltaBeta = Math.round(2 * DeltaBeta, 0);

                        //oben
                        pixi.graphics.lineStyle(1, pixiApi.colors.yellow);
                        pixi.graphics.arc(360 + center_L + decXScale, -decYScale + segRaduppL / (scale / 100), segRaduppL / (scale / 100), -((StartAlpha / 180) * Math.PI), -(((StartAlpha + DeltaAlpha) / 180) * Math.PI), true);
                        //unten
                        pixi.graphics.arc(360 + center_L + decXScale, -decYScale + segRadlowL / (scale / 100) - (segBoxheightL / 2) / (scale / 100), segRadlowL / (scale / 100), -((StartBeta / 180) * Math.PI), -(((StartBeta + DeltaBeta) / 180) * Math.PI), true);
                    }// else if (sTypeR == "1") {                         // Straight segment
                    //}

                    //T
                    pixi.graphics.lineStyle(1, pixiApi.colors.yellow);
                    pixi.graphics.drawCrispLine(center_L + decXScale, -decYScale + 5, center_L + decXScale, -decYScale);
                    pixi.graphics.drawCrispLine(center_L + decXScale - 5, -decYScale, center_L + decXScale + 5, -decYScale);
                }

                //Schnitt
                //         jgCalc.setColor("green");
                //         jgCalc.drawPolyline(arrXCutV11_L, arrYCutV11_L);
                //         jgCalc.drawPolyline(arrXCutV12_L, arrYCutV12_L);
                //         jgCalc.drawLine(arrXCutV11_L[0], 0, arrXCutV11_L[0], arrYCutV11_L[0]);
                //         jgCalc.drawLine(arrXCutV12_L[0], 0, arrXCutV12_L[0], arrYCutV12_L[0]);
                //         jgCalc.drawLine(arrXCutV11_L[arrXCutV11_L.length-1], arrYCutV11_L[arrYCutV11_L.length-1], arrXCutV12_L[arrXCutV12_L.length-1], arrYCutV12_L[arrYCutV12_L.length-1]);
                //         jgCalc.drawString(Math.round(topThkL/100*10)/10, arrXCutV11_L[arrXCutV11_L.length-1] - 10, arrYCutV11_L[arrXCutV11_L.length-1] - 20);
                //
                //         jgCalc.drawPolyline(arrXCutV21_L, arrYCutV21_L);
                //         jgCalc.drawPolyline(arrXCutV22_L, arrYCutV22_L);
                //         jgCalc.drawLine(arrXCutV21_L[0], 0, arrXCutV21_L[0], arrYCutV21_L[0]);
                //         jgCalc.drawLine(arrXCutV22_L[0], 0, arrXCutV22_L[0], arrYCutV22_L[0]);
                //         jgCalc.drawLine(arrXCutV21_L[arrXCutV21_L.length-1], arrYCutV21_L[arrYCutV21_L.length-1], arrXCutV22_L[arrXCutV12_L.length-1], arrYCutV22_L[arrYCutV22_L.length-1]);
                //         jgCalc.drawString(Math.round(downThkL/100*10)/10, arrXCutV21_L[arrXCutV21_L.length-1] - 10, arrYCutV21_L[arrYCutV21_L.length-1]);
                //
                //         jgCalc.drawPolyline(arrXCutH11_L, arrYCutH11_L);
                //         jgCalc.drawPolyline(arrXCutH12_L, arrYCutH12_L);
                //         jgCalc.drawLine(arrXCutH11_L[0], arrYCutH11_L[0], center_L, arrYCutH11_L[0]);
                //         jgCalc.drawLine(arrXCutH12_L[0], arrYCutH12_L[0], center_L, arrYCutH12_L[0]);
                //         jgCalc.drawLine(arrXCutH11_L[arrXCutH11_L.length-1], arrYCutH11_L[arrYCutH11_L.length-1], arrXCutH12_L[arrXCutH12_L.length-1], arrYCutH12_L[arrYCutH12_L.length-1]);
                //         jgCalc.drawString(Math.round(nasalThkL/100*10)/10, arrXCutH11_L[arrXCutH11_L.length-1] - 10, arrYCutH11_L[arrYCutH11_L.length-1]);
                //
                //         jgCalc.drawPolyline(arrXCutH21_L, arrYCutH21_L);
                //         jgCalc.drawPolyline(arrXCutH22_L, arrYCutH22_L);
                //         jgCalc.drawLine(arrXCutH21_L[0], arrYCutH21_L[0], center_L, arrYCutH21_L[0]);
                //         jgCalc.drawLine(arrXCutH22_L[0], arrYCutH22_L[0], center_L, arrYCutH22_L[0]);
                //         jgCalc.drawLine(arrXCutH21_L[arrXCutH21_L.length-1], arrYCutH21_L[arrYCutH21_L.length-1], arrXCutH22_L[arrXCutH22_L.length-1], arrYCutH22_L[arrYCutH22_L.length-1]);
                //         jgCalc.drawString(Math.round(tempThkL/100*10)/10, arrXCutH21_L[arrXCutH21_L.length-1] - 10, arrYCutH21_L[arrYCutH21_L.length-1]);

                //dickesten Punkt
                pixi.graphics.lineStyle(0, pixiApi.colors.white);
                pixi.graphics.beginFill(pixiApi.colors.red)
                pixi.graphics.drawEllipse(arrXmaxThkL, arrYmaxThkL, 4, 4);
                var diffX = 0;
                var diffY = 0;
                if (arrXmaxThkL > center_L) {
                    diffX = -10;
                } else {
                    diffX = 0;
                }
                if (arrYmaxThkL < 0) {
                    diffY = -25;
                } else {
                    diffY = 10;
                }
                maxThkLStr = (Math.round(parseInt(maxCalcThicknL) / 100 * 10) / 10).toFixed(1); //maxThkL
                if (maxThkLStr == "-Infinity") {
                    maxThkLStr = "0";
                }
                pixi.text.drawString(maxThkLStr, arrXmaxThkL + diffX, arrYmaxThkL + diffY, pixiApi.colors.red);

                //dünnsten Punkt
                pixi.graphics.beginFill(pixiApi.colors.red)
                pixi.graphics.drawEllipse(arrXminThkL, arrYminThkL, 3, 3);
                if (arrXminThkL > center_L) {
                    diffX = -10;
                } else {
                    diffX = 0;
                }
                if (arrYminThkL < 0) {
                    diffY = -25;
                } else {
                    diffY = 15;
                }
                minThkLStr = (Math.round(parseInt(minCalcThicknL) / 100 * 10) / 10).toFixed(1); //minThkL
                if (minThkLStr == "Infinity") {
                    minThkLStr = "0";
                }
                pixi.text.drawString(minThkLStr, arrXminThkL + diffX, arrYminThkL + diffY, pixiApi.colors.red);
            }
        }

        // Assign values to calculation fields
        self.calculation.right.minimal = minThkRStr
        self.calculation.right.maximal = maxThkRStr
        self.calculation.right.top = (Math.round(topThkR / 100 * 10) / 10).toFixed(1);
        self.calculation.right.down = (Math.round(downThkR / 100 * 10) / 10).toFixed(1);
        self.calculation.right.nasal = (Math.round(nasalThkR / 100 * 10) / 10).toFixed(1);
        self.calculation.right.temporal = (Math.round(tempThkR / 100 * 10) / 10).toFixed(1);

        self.calculation.left.minimal = minThkLStr
        self.calculation.left.maximal = maxThkLStr
        self.calculation.left.top = (Math.round(topThkL / 100 * 10) / 10).toFixed(1);
        self.calculation.left.down = (Math.round(downThkL / 100 * 10) / 10).toFixed(1);
        self.calculation.left.nasal = (Math.round(nasalThkL / 100 * 10) / 10).toFixed(1);
        self.calculation.left.temporal = (Math.round(tempThkL / 100 * 10) / 10).toFixed(1);

        self.calculation.areGraphicsSuccessfullyRendered = true

        return pixi
    },

    /**
     * Draws the lens graphics inside of the Form and Geometry component.
     */
    drawFormAndGeometryLenses(drawingContainerID, graphicsApp = null, parentContext) {
        const getValOrZero = (obj, prop, zero = 0) => {
            let r = Helpers.getObjProp(obj, prop)
            return r != "" ? parseFloat(r) : zero
        }
        if (!self.form.shape.current.decodedData) return

        try {
            // Create or get Pixi app
            var pixi = graphicsApp
            if (pixi === undefined || pixi === null)
                pixi = pixiApi.createNewPixiApp(drawingContainerID)
            else pixi.reset()

            // Handle responsiveness for different screen sizes
            const defaultSize = 2100 // larger number = smaller drawing
            const fit = () => {
                pixi.app.renderer.resize(pixi.app.view.parentNode.clientWidth, pixi.app.view.parentNode.clientHeight)
                pixi.container.transform.position.x = pixi.app.screen.width/2
                pixi.container.transform.position.y = pixi.app.screen.height/2
                pixi.container.transform.scale.set(pixi.app.view.parentNode.clientWidth / defaultSize)

                // Scaling
                const scale = pixi.app.view.parentNode.clientWidth / defaultSize
                pixi.container.transform.scale.set(scale)
                for (let i = 0; i < pixi.text.container.children.length; i++) {
                    const child = pixi.text.container.children[i];

                    // Keep text scale the same
                    child.transform.scale.set( 0.30 / scale)
                }
            }

            setTimeout(() => {
                fit()
            }, 1);
            // Executed in FormAndGeometry.vue in onScreenResize function
            parentContext.graphicsScreenResizeHook = () => {
                fit()
            }
        } catch (error) {
            console.error(error)
            return
        }

        var scale = 10;
        var mathpi = Math.PI / -180;

        var dbl = self.formAndGeometry.dbl ? self.formAndGeometry.dbl : 18
        var dblScale = (parseInt(dbl) * dpimm) / 2;
        dblScale = dblScale / (scale / 100) / 4


        // Right side
        let lensGeoR = Helpers.getObjProp(self.erp.right.getLensGeo, 'LensGeo')
        if (lensGeoR && lensGeoR !== "") lensGeoR = lensGeoR[0]

        var decX_R = getValOrZero(lensGeoR, 'DECX', 0)
        var decY_R = getValOrZero(lensGeoR, 'DECY', 0)
        var groupR = getValOrZero(lensGeoR, 'LGROUP', -1)
        var sTypeR = getValOrZero(lensGeoR, 'STYPE', 0)
        var asphereR = getValOrZero(lensGeoR, 'ASHERE', -1)
        var stockR = getValOrZero(lensGeoR, 'STOCK', -1)
        var segBoxwidthR = getValOrZero(lensGeoR, 'SEGWIDTH', -1)
        var segBoxheightR = getValOrZero(lensGeoR, 'SEGHEIGHT', -1)
        var segRaduppR = getValOrZero(lensGeoR, 'SEGUPPRAD', -1)
        var segRadlowR = getValOrZero(lensGeoR, 'SEGLOWRAD', -1)
        var PRP_X_R = getValOrZero(lensGeoR, 'PRP_X', 0)
        var PRP_Y_R = getValOrZero(lensGeoR, 'PRP_Y', 0)
        var DRP_X_R = getValOrZero(lensGeoR, 'DRP_X', 0)
        var DRP_Y_R = getValOrZero(lensGeoR, 'DRP_Y', 0)
        var LRP_X_R = getValOrZero(lensGeoR, 'LRP_X', 0)
        var LRP_Y_R = getValOrZero(lensGeoR, 'LRP_Y', 0)
        var NRPMIN_X_R = getValOrZero(lensGeoR, 'NRPMIN_X', 0)
        var NRPMIN_Y_R = getValOrZero(lensGeoR, 'NRPMIN_Y', 0)
        var NRPMAX_X_R = getValOrZero(lensGeoR, 'NRPMAX_X', 0)
        var NRPMAX_Y_R = getValOrZero(lensGeoR, 'NRPMAX_Y', 0)
        if(self.individual.right && self.individual.right.definedFields && self.individual.right.definedFields.length > 0) {
          const selectedInsetR = self.individual.right.definedFields.find(x => x.name == 'INSET')
          if (selectedInsetR && selectedInsetR.value !== '') {
            NRPMAX_X_R = parseFloat(selectedInsetR.value)
            NRPMIN_X_R = parseFloat(selectedInsetR.value)
          }
        }

        var FCOC_X_R = getValOrZero(lensGeoR, 'FCOC_X', 0)
        var FCOC_Y_R = getValOrZero(lensGeoR, 'FCOC_Y', 0)
        var FCSG_X_R = getValOrZero(lensGeoR, 'FCSG_X', 0)
        var FCSG_Y_R = getValOrZero(lensGeoR, 'FCSG_Y', 0)
        var FCBC_X_R = getValOrZero(lensGeoR, 'FCBC_X', 0)
        var FCBC_Y_R = getValOrZero(lensGeoR, 'FCBC_Y', 0)


        // Left side
        let lensGeoL = Helpers.getObjProp(self.erp.left.getLensGeo, 'LensGeo')
        if (lensGeoL && lensGeoL !== "") lensGeoL = lensGeoL[0]

        var decX_L = getValOrZero(lensGeoL, 'DECX', 0)
        var decY_L = getValOrZero(lensGeoL, 'DECY', 0)
        var groupL = getValOrZero(lensGeoL, 'LGROUP', -1)
        var sTypeL = getValOrZero(lensGeoL, 'STYPE', 0)
        var asphereL = getValOrZero(lensGeoL, 'ASHERE', -1)
        var stockL = getValOrZero(lensGeoL, 'STOCK', -1)
        var segBoxwidthL = getValOrZero(lensGeoL, 'SEGWIDTH', -1)
        var segBoxheightL = getValOrZero(lensGeoL, 'SEGHEIGHT', -1)
        var segRaduppL = getValOrZero(lensGeoL, 'SEGUPPRAD', -1)
        var segRadlowL = getValOrZero(lensGeoL, 'SEGLOWRAD', -1)
        var PRP_X_L = getValOrZero(lensGeoL, 'PRP_X', 0)
        var PRP_Y_L = getValOrZero(lensGeoL, 'PRP_Y', 0)
        var DRP_X_L = getValOrZero(lensGeoL, 'DRP_X', 0)
        var DRP_Y_L = getValOrZero(lensGeoL, 'DRP_Y', 0)
        var LRP_X_L = getValOrZero(lensGeoL, 'LRP_X', 0)
        var LRP_Y_L = getValOrZero(lensGeoL, 'LRP_Y', 0)
        var NRPMIN_X_L = getValOrZero(lensGeoL, 'NRPMIN_X', 0)
        var NRPMIN_Y_L = getValOrZero(lensGeoL, 'NRPMIN_Y', 0)
        var NRPMAX_X_L = getValOrZero(lensGeoL, 'NRPMAX_X', 0)
        var NRPMAX_Y_L = getValOrZero(lensGeoL, 'NRPMAX_Y', 0)
        if(self.individual.left && self.individual.left.definedFields && self.individual.left.definedFields.length > 0) {
          const selectedInsetR = self.individual.left.definedFields.find(x => x.name == 'INSET')
          if (selectedInsetR && selectedInsetR.value !== '') {
            NRPMAX_X_L = parseFloat(selectedInsetR.value)
            if(NRPMAX_X_L > 0) NRPMAX_X_L = -NRPMAX_X_L

            NRPMIN_X_L = parseFloat(selectedInsetR.value)
            if(NRPMIN_X_L > 0) NRPMIN_X_L = -NRPMIN_X_L
          }
        }

        var FCOC_X_L = getValOrZero(lensGeoL, 'FCOC_X', 0)
        var FCOC_Y_L = getValOrZero(lensGeoL, 'FCOC_Y', 0)
        var FCSG_X_L = getValOrZero(lensGeoL, 'FCSG_X', 0)
        var FCSG_Y_L = getValOrZero(lensGeoL, 'FCSG_Y', 0)
        var FCBC_X_L = getValOrZero(lensGeoL, 'FCBC_X', 0)
        var FCBC_Y_L = getValOrZero(lensGeoL, 'FCBC_Y', 0)

        //forcierte Progessionslänge
        let proglength = 0
        if (self.individual.definedFields !== undefined &&
            self.individual.definedFields !== null &&
            self.individual.definedFields.length !== undefined &&
            self.individual.definedFields.length > 0
        ){
            const selectedProglength = self.individual.definedFields.find(x => x.name == 'PROGRESSIONLENGTH')

            if (selectedProglength && selectedProglength !== undefined)
                proglength = parseFloat(selectedProglength.value) || 0
        }
        if (proglength > 0) {
            var OCSG_Y_R = 0;
            var OCSG_Y_L = 0;
            if(store.getters.getIniProperties['SETTINGS_SHOW_LENS_ORDER'] === 'PROGLEN'){
                OCSG_Y_R = LRP_Y_R - PRP_Y_R;
                OCSG_Y_L = LRP_Y_L - PRP_Y_L;
            }
            // <?php
            //    if (isset($_SESSION['corrmode']) && $_SESSION['corrmode'] = 'PROGLEN') {
            //       echo "OCSG_Y_R = LRP_Y_R - PRP_Y_R;";
            //       echo "OCSG_Y_L = LRP_Y_L - PRP_Y_L;";
            //    }
            // ?>
            NRPMIN_Y_R = -proglength + OCSG_Y_R;
            NRPMIN_Y_L = -proglength + OCSG_Y_L;
            NRPMAX_Y_R = -proglength + OCSG_Y_R;
            NRPMAX_Y_L = -proglength + OCSG_Y_L;
        }

        var minDiamR = getValOrZero(lensGeoR, 'MINDM', 0)
        var minDiamL = getValOrZero(lensGeoL, 'MINDM', 0)

        var center_R = 0;
        var center_L = 0;

        var arrX_R = new Array();
        var arrY_R = new Array();
        var arrX_L = new Array();
        var arrY_L = new Array();

        var maxX_R = 0;
        var minX_R = 0;
        var maxY_R = 0;
        var minY_R = 0;

        var maxX_L = 0;
        var minX_L = 0;
        var maxY_L = 0;
        var minY_L = 0;

        var maxX_R_Org = 0;
        var minX_L_Org = 0;

        let arrR = self.form.shape.current.decodedData.right.rValues
        let arrL = self.form.shape.current.decodedData.left.rValues

        //erstmal Breite berechen (Rechts)
        for (var i = 0; i < arrR.length; i++) {
            arrX_R[i] = Math.cos(i * mathpi) * (arrR[i] / scale);
            arrY_R[i] = Math.sin(i * mathpi) * (arrR[i] / scale);

            if (arrX_R[i] > maxX_R_Org) {
                maxX_R_Org = arrX_R[i];
            }
        }

        maxX_R = -maxX_R_Org;

        //dann komplett mit DBL zeichen
        const arr_R_polygon = []
        for (var i = 0; i < arrR.length; i++) {
            const x = Math.cos(i * mathpi) * (arrR[i] / scale) - maxX_R_Org - dblScale;
            const y = Math.sin(i * mathpi) * (arrR[i] / scale);
            arrX_R[i] = x
            arrY_R[i] = y
            arr_R_polygon.push(x,y)

            if (arrX_R[i] > maxX_R) {
                maxX_R = arrX_R[i];
            } else if (arrX_R[i] < minX_R) {
                minX_R = arrX_R[i];
            }

            if (arrY_R[i] > maxY_R) {
                maxY_R = arrY_R[i];
            } else if (arrY_R[i] < minY_R) {
                minY_R = arrY_R[i];
            }
        }

        var center_R = (minX_R - maxX_R) / 2 - dblScale;

        //erstmal Breite berechen (Links)
        for (var i = 0; i < arrL.length; i++) {
            arrX_L[i] = Math.cos(i * mathpi) * (arrL[i] / scale);
            arrY_L[i] = Math.sin(i * mathpi) * (arrL[i] / scale);

            if (arrX_L[i] < minX_L_Org) {
                minX_L_Org = arrX_L[i];
            }
        }

        minX_L = -minX_L_Org;

        //dann komplett mit DBL zeichen
        const arr_L_polygon = []
        for (var i = 0; i < arrL.length; i++) {
            const x = Math.cos(i * mathpi) * (arrL[i] / scale) - minX_L_Org + dblScale;
            const y = Math.sin(i * mathpi) * (arrL[i] / scale);
            arrX_L[i] = x
            arrY_L[i] = y
            arr_L_polygon.push(x,y)


            if (arrX_L[i] > maxX_L) {
                maxX_L = arrX_L[i];
            } else if (arrX_L[i] < minX_L) {
                minX_L = arrX_L[i];
            }

            if (arrY_L[i] > maxY_L) {
                maxY_L = arrY_L[i];
            } else if (arrY_L[i] < minY_L) {
                minY_L = arrY_L[i];
            }
        }

        //Form
        pixi.graphics.beginFill(self.iniProperties.lens_graphics_color)
        pixi.graphics.drawPolygon(arr_L_polygon)
        pixi.graphics.drawPolygon(arr_R_polygon)
        pixi.graphics.endFill()

        const yellowLinesWidth = 2
        const lineEndScale = 2

        //Rechts
        if (arrR.length > 0) {
            var center_R = (minX_R - maxX_R) / 2 - dblScale ;

            //Mittelpunkt
            pixi.graphics.lineStyle(2, pixiApi.colors.white);
            pixi.graphics.drawLine(center_R, - 10 * lineEndScale, center_R, 10 * lineEndScale);
            pixi.graphics.drawLine(center_R - 10 * lineEndScale, 0, center_R + 10 * lineEndScale, 0);

            //VBox
            pixi.graphics.lineStyle(1, pixiApi.colors.darkgreen);
            pixi.graphics.drawLine(- dblScale, -maxY_R - 20 * lineEndScale, - dblScale, -minY_R + 20 * lineEndScale);
            pixi.graphics.drawLine(minX_R, -maxY_R - 20 * lineEndScale, minX_R, -minY_R + 20 * lineEndScale);

            //HBox
            pixi.graphics.drawLine(minX_R - 20 * lineEndScale, maxY_R, 0, maxY_R);
            pixi.graphics.drawLine(minX_R - 20 * lineEndScale, minY_R, 0, minY_R);

            //Text
            var hbox_R = Math.round((minX_R - maxX_R) * -1 * scale) / 100;
            pixi.text.drawString(hbox_R.toFixed(1), (minX_R - maxX_R) / 2 - dblScale - 20, -maxY_R - 20);
            if (!self.formAndGeometry.right.boxWidth || isNaN(self.formAndGeometry.right.boxWidth)) {
                self.formAndGeometry.right.boxWidth = hbox_R.toFixed(1)
            }

            var vbox_R = Math.round((maxY_R + minY_R * -1) * scale) / 100;
            pixi.text.drawString(vbox_R.toFixed(1), minX_R - 30, -10);
            if (!self.formAndGeometry.right.boxHeight || isNaN(self.formAndGeometry.right.boxHeight))
                self.formAndGeometry.right.boxHeight = vbox_R.toFixed(1)

            if (!self.formAndGeometry.right.height && Helpers.getObjProp(self.productCategory.selectedGroup, 'Value', null) == "4") {
                self.formAndGeometry.right.height = Math.round(vbox_R / 2);
            }

            var pdr_org = self.refraction.right.pd;
            var heightr_org = self.refraction.right.height

            if (pdr_org !== "" && heightr_org !== "") {
                if (groupR == 0) {                          //Single Vision
                    //optischer Bezugspunkt "SG"
                    pixi.graphics.beginFill(pixiApi.colors.yellow)
                    pixi.graphics.drawEllipse(center_R + FCSG_X_R / (scale / 100), -FCSG_Y_R / (scale / 100), 2, 2);
                } else if (groupL == 1 || groupL == 2) {   //Bifocal
                    //optische Zentrum
                    pixi.graphics.beginFill(pixiApi.colors.yellow)
                    pixi.graphics.drawEllipse(center_R + (FCOC_X_R) / (scale / 100), -(FCOC_Y_R) / (scale / 100), 2, 2);

                    if (sTypeR == "0") {                        // Curved Segment
                        var C = segRaduppR + segRadlowR - segBoxheightR;
                        var S = (segRaduppR + segRadlowR + C) / 2;
                        var R = Math.sqrt(((S - segRadlowR) * (S - segRaduppR) * (S - C)) / S);

                        var DeltaAlpha = (2 * Math.atan(R / (S - segRadlowR))) * (180 / Math.PI);
                        var DeltaBeta = (2 * Math.atan(R / (S - segRaduppR))) * (180 / Math.PI);

                        var StartAlpha = Math.round(90 - DeltaAlpha, 0);
                        var StartBeta = Math.round(270 - DeltaBeta, 0);

                        DeltaAlpha = Math.round(2 * DeltaAlpha, 0);
                        DeltaBeta = Math.round(2 * DeltaBeta, 0);

                        //oben
                        pixi.graphics.lineStyle(yellowLinesWidth, pixiApi.colors.yellow);
                        pixi.graphics.arc(center_R + FCSG_X_R / (scale / 100), -(FCSG_Y_R - segRaduppR) / (scale / 100), segRaduppR / (scale / 100), -((StartAlpha / 180) * Math.PI), -(((StartAlpha + DeltaAlpha) / 180) * Math.PI), true);
                        //unten
                        pixi.graphics.arc(center_R + FCSG_X_R / (scale / 100), -(FCSG_Y_R - segRadlowR + segBoxheightR / 2) / (scale / 100), segRadlowR / (scale / 100), -((StartBeta / 180) * Math.PI), -(((StartBeta + DeltaBeta) / 180) * Math.PI), true);
                    } else if (sTypeR == "1") {                         // Straight segment
                        var DeltaBeta;
                        var StartBeta;
                        if (!(segRadlowR == 0 || segBoxheightR == 0)) {
                            if (segBoxheightR > segRadlowR) {
                                DeltaBeta = Math.acos((segBoxheightR - segRadlowR) / segRadlowR) * (180 / Math.PI);
                                StartBeta = Math.round(90 + DeltaBeta, 0);
                                DeltaBeta = Math.round(360 - (2 * DeltaBeta), 0);
                            } else if (segBoxheightR == segRadlowR) {
                                DeltaBeta = 180;
                                StartBeta = 180;
                            } else {
                                DeltaBeta = Math.acos((segRadlowR - segBoxheightR) / segRadlowR) * (180 / Math.PI);;
                                StartBeta = Math.round(270 - DeltaBeta, 0);;
                                DeltaBeta = Math.round(2 * DeltaBeta, 0);;
                            }
                        }
                        //oben
                        var x1 = Math.cos(StartBeta * mathpi) * (segRadlowR / (scale / 100));
                        var y1 = Math.sin(StartBeta * mathpi) * (segRadlowR / (scale / 100) - (segBoxheightR / 2) / (scale / 100));
                        var x2 = Math.cos((StartBeta + DeltaBeta) * mathpi) * (segRadlowR / (scale / 100));

                        pixi.graphics.lineStyle(yellowLinesWidth, pixiApi.colors.yellow);
                        pixi.graphics.drawLine(
                            center_R + FCSG_X_R / (scale / 100) + x1, -FCSG_Y_R / (scale / 100) - y1,
                            center_R + FCSG_X_R / (scale / 100) + x2, -FCSG_Y_R / (scale / 100) - y1
                        )
                        //unten
                        pixi.graphics.arc(center_R + FCSG_X_R / (scale / 100), -(FCSG_Y_R - segRadlowR + segBoxheightR / 2) / (scale / 100), segRadlowR / (scale / 100), -((StartBeta / 180) * Math.PI), -(((StartBeta + DeltaBeta) / 180) * Math.PI), true);
                    }

                    //T
                    pixi.graphics.lineStyle(yellowLinesWidth, pixiApi.colors.yellow);
                    pixi.graphics.drawLine(center_R + FCSG_X_R / (scale / 100), -FCSG_Y_R / (scale / 100) + 5 * lineEndScale, center_R + FCSG_X_R / (scale / 100), -FCSG_Y_R / (scale / 100));
                    pixi.graphics.drawLine(center_R + FCSG_X_R / (scale / 100) - 5 * lineEndScale, -FCSG_Y_R / (scale / 100), center_R + FCSG_X_R / (scale / 100) + 5 * lineEndScale, -FCSG_Y_R / (scale / 100));
                } else if (groupR == 3 || groupR == 4) {   //Progressive or degressive
                    //Segmentbezugspunkt
                    pixi.graphics.lineStyle(yellowLinesWidth, pixiApi.colors.yellow);
                    pixi.graphics.drawLine(center_R + FCSG_X_R / (scale / 100), -FCSG_Y_R / (scale / 100) - 5 * lineEndScale, center_R + FCSG_X_R / (scale / 100), -FCSG_Y_R / (scale / 100) + 5 * lineEndScale);
                    pixi.graphics.drawLine(center_R + FCSG_X_R / (scale / 100) - 5 * lineEndScale, -FCSG_Y_R / (scale / 100), center_R + FCSG_X_R / (scale / 100) + 5 * lineEndScale, -FCSG_Y_R / (scale / 100));

                    //optische Zentrum
                    pixi.graphics.beginFill(pixiApi.colors.yellow)
                    pixi.graphics.drawEllipse(center_R + FCOC_X_R / (scale / 100), -FCOC_Y_R / (scale / 100), 2, 2);

                    //Fernbezugspunkt
                    pixi.graphics.arc(center_R + (FCSG_X_R - LRP_X_R + DRP_X_R) / (scale / 100), - (FCSG_Y_R - LRP_Y_R + DRP_Y_R) / (scale / 100), 25 * (25 / scale), -((300 / 180) * Math.PI), -((240 / 180) * Math.PI), true)

                    //Nahbezugspunkt
                    pixi.graphics.arc(center_R + (FCSG_X_R - LRP_X_R + NRPMIN_X_R) / (scale / 100), - (FCSG_Y_R - LRP_Y_R + NRPMIN_Y_R) / (scale / 100), 3 / (scale / 100), -Math.PI / 2, -Math.PI, true)
                    pixi.graphics.arc(center_R + (FCSG_X_R - LRP_X_R + NRPMAX_X_R) / (scale / 100), - (FCSG_Y_R - LRP_Y_R + NRPMAX_Y_R) / (scale / 100), 3 / (scale / 100), 0, -Math.PI / 2, true)
                    pixi.graphics.arc(center_R + (FCSG_X_R - LRP_X_R + NRPMAX_X_R) / (scale / 100), - (FCSG_Y_R - LRP_Y_R + NRPMAX_Y_R) / (scale / 100), 3 / (scale / 100), Math.PI / 2, 0, true)
                    pixi.graphics.arc(center_R + (FCSG_X_R - LRP_X_R + NRPMIN_X_R) / (scale / 100), - (FCSG_Y_R - LRP_Y_R + NRPMIN_Y_R) / (scale / 100), 3 / (scale / 100), -Math.PI, Math.PI / 2, true)
                }

                // let decX = (Math.round(decX_R*10) / 10)
                // let decY = (Math.round(decY_R*10) / 10)
                // formAndGeometry.right.decentrationX = isNaN(decX) ? 0 : decX
                // formAndGeometry.right.decentrationY = isNaN(decY) ? 0 : decY
            }

            //Blank
            pixi.graphics.lineStyle(1, pixiApi.colors.red);
            var diamr = minDiamR / (scale / 100);
            var decX_RScale = (FCBC_X_R) / (scale / 100);
            var decY_RScale = (FCBC_Y_R) / (scale / 100);

            pixi.graphics.beginFill(pixiApi.colors.red)
            pixi.graphics.drawEllipse(center_R + decX_RScale, - decY_RScale, 2, 2);
            pixi.graphics.drawEllipse(center_R + decX_RScale, - decY_RScale, diamr/2, diamr/2);

            //Drill
            let side = 'right'
            self.formAndGeometry.drillHoles.map(drillHole => {
                if (drillHole[side].startX && drillHole[side].startY) {
                    if (isNaN(drillHole[side].diam || !drillHole[side].diam)) {
                        drillHole.right.diam = "1.00"
                        drillHole.left.diam = "1.00"
                    }

                    var drillsX1Rnew = this.calcDrill(drillHole[side].direction, drillHole[side].startX, drillHole[side].startY, arrX_R, arrY_R, scale)
                    var drilleX1Rnew = this.calcDrill(drillHole[side].direction, drillHole[side].endX, drillHole[side].endY, arrX_R, arrY_R, scale)

                    if (drillsX1Rnew == 0 || drilleX1Rnew == 0) {
                        store.dispatch('notify', {
                            message: store.getters.translations.notifications.outOfDelivery
                        })
                        drillHole[side].startX = null
                        drillHole[side].startY = null
                        drillHole[side].endX = null
                        drillHole[side].endY = null
                    }
                    else {
                        this.drawDrill(
                            pixi.graphics,
                            drillsX1Rnew,
                            drillHole[side].startY,
                            drilleX1Rnew,
                            drillHole[side].endY,
                            drillHole[side].diam,
                            scale,
                        )
                    }
                }
            })
        }

        //Links
        if (arrL.length > 0) {
            var center_L = maxX_L / 2 + dblScale / 2;

            //Mittelpunkt
            pixi.graphics.lineStyle(2, pixiApi.colors.white);
            pixi.graphics.drawLine(center_L, - 10 * lineEndScale, center_L, 10 * lineEndScale);
            pixi.graphics.drawLine(center_L - 10 * lineEndScale, 0, center_L + 10 * lineEndScale, 0);

            //VBox
            pixi.graphics.lineStyle(1, pixiApi.colors.darkgreen);
            pixi.graphics.drawLine(dblScale, -maxY_L - 20 * lineEndScale, dblScale, -minY_L + 20 * lineEndScale);
            pixi.graphics.drawLine(maxX_L, -maxY_L - 20 * lineEndScale, maxX_L, -minY_L + 20 * lineEndScale);

            //HBox
            pixi.graphics.drawLine(0, maxY_L, maxX_L + 20 * lineEndScale, maxY_L);
            pixi.graphics.drawLine(0, minY_L, maxX_L + 20 * lineEndScale, minY_L);

            //Text
            var hbox_L = Math.round((maxX_L + minX_L * -1) * scale) / 100;
            pixi.text.drawString(hbox_L.toFixed(1), center_L + 20, minY_L - 20);
            if (!self.formAndGeometry.left.boxWidth || isNaN(self.formAndGeometry.left.boxWidth)) {
                self.formAndGeometry.left.boxWidth = hbox_L.toFixed(1)
            }

            var vbox_L = Math.round((maxY_L + minY_L * -1) * scale) / 100;
            pixi.text.drawString(vbox_L.toFixed(1), maxX_L + 30, -10);
            if (!self.formAndGeometry.left.boxHeight || isNaN(self.formAndGeometry.left.boxHeight))
                self.formAndGeometry.left.boxHeight = vbox_L.toFixed(1)

            if (!self.formAndGeometry.left.height && Helpers.getObjProp(self.productCategory.selectedGroup, 'Value', null) == "4") {
                self.formAndGeometry.left.height = Math.round(vbox_L / 2);
            }

            var pdl_org = self.refraction.left.pd;
            var heightl_org = self.refraction.left.height

            if (pdl_org !== "" && heightl_org !== "") {
                if (groupL == 0) {                          //Single Vision
                    //optischer Bezugspunkt "SG"
                    pixi.graphics.beginFill(pixiApi.colors.yellow)
                    pixi.graphics.drawEllipse(center_L - FCSG_X_L / (scale / 100), -FCSG_Y_L / (scale / 100), 2, 2);
                } else if (groupL == 1 || groupL == 2) {   //Bifocal
                    //optische Zentrum
                    pixi.graphics.beginFill(pixiApi.colors.yellow)
                    pixi.graphics.drawEllipse(center_L - FCOC_X_L / (scale / 100), -FCOC_Y_L / (scale / 100), 2, 2);

                    if (sTypeL == "0") {                        // Curved Segment
                        var C = segRaduppL + segRadlowL - segBoxheightL;
                        var S = (segRaduppL + segRadlowL + C) / 2;
                        var R = Math.sqrt(((S - segRadlowL) * (S - segRaduppL) * (S - C)) / S);

                        var DeltaAlpha = (2 * Math.atan(R / (S - segRadlowL))) * (180 / Math.PI);
                        var DeltaBeta = (2 * Math.atan(R / (S - segRaduppL))) * (180 / Math.PI);

                        var StartAlpha = Math.round(90 - DeltaAlpha, 0);
                        var StartBeta = Math.round(270 - DeltaBeta, 0);

                        DeltaAlpha = Math.round(2 * DeltaAlpha, 0);
                        DeltaBeta = Math.round(2 * DeltaBeta, 0);

                        //oben
                        pixi.graphics.lineStyle(yellowLinesWidth, pixiApi.colors.yellow);
                        pixi.graphics.arc(center_L - FCSG_X_L / (scale / 100), - (FCSG_Y_L - segRaduppL) / (scale / 100), segRaduppL / (scale / 100), -((StartAlpha / 180) * Math.PI), -(((StartAlpha + DeltaAlpha) / 180) * Math.PI), true);
                        // unten
                        pixi.graphics.arc(center_L - FCSG_X_L / (scale / 100), - (FCSG_Y_L - segRadlowL) / (scale / 100) - (segBoxheightL / 2) / (scale / 100), segRadlowL / (scale / 100), -((StartBeta / 180) * Math.PI), -(((StartBeta + DeltaBeta) / 180) * Math.PI), true);
                    } else if (sTypeL == "1") {                         // Straight segment
                        var DeltaBeta;
                        var StartBeta;
                        if (!(segRadlowL == 0 || segBoxheightL == 0)) {
                            if (segBoxheightL > segRadlowL) {
                                DeltaBeta = Math.acos((segBoxheightL - segRadlowL) / segRadlowL) * (180 / Math.PI);
                                StartBeta = Math.round(90 + DeltaBeta, 0);
                                DeltaBeta = Math.round(360 - (2 * DeltaBeta), 0);
                            } else if (segBoxheightL == segRadlowL) {
                                DeltaBeta = 180;
                                StartBeta = 180;
                            } else {
                                DeltaBeta = Math.acos((segRadlowL - segBoxheightL) / segRadlowL) * (180 / Math.PI);;
                                StartBeta = Math.round(270 - DeltaBeta, 0);;
                                DeltaBeta = Math.round(2 * DeltaBeta, 0);;
                            }
                        }
                        //oben
                        var x1 = Math.cos(StartBeta * mathpi) * (segRadlowL / (scale / 100));
                        var y1 = Math.sin(StartBeta * mathpi) * (segRadlowL / (scale / 100) - (segBoxheightL / 2) / (scale / 100));
                        var x2 = Math.cos((StartBeta + DeltaBeta) * mathpi) * (segRadlowL / (scale / 100));

                        pixi.graphics.lineStyle(yellowLinesWidth, pixiApi.colors.yellow);
                        pixi.graphics.drawLine(
                            center_L - FCSG_X_L / (scale / 100) + x1, - FCSG_Y_L / (scale / 100) - y1,
                            center_L - FCSG_X_L / (scale / 100) + x2, - FCSG_Y_L / (scale / 100) - y1
                        )
                        //unten
                        pixi.graphics.arc(center_L - FCSG_X_L / (scale / 100), - (FCSG_Y_L - segRadlowL) / (scale / 100) - (segBoxheightL / 2) / (scale / 100), segRadlowL / (scale / 100), -((StartBeta / 180) * Math.PI), -(((StartBeta + DeltaBeta) / 180) * Math.PI), true);
                    }

                    //T
                    pixi.graphics.lineStyle(yellowLinesWidth, pixiApi.colors.yellow);
                    pixi.graphics.drawLine(center_L - FCSG_X_L / (scale / 100), -FCSG_Y_L / (scale / 100) + 5 * lineEndScale, center_L - FCSG_X_L / (scale / 100), -FCSG_Y_L / (scale / 100));
                    pixi.graphics.drawLine(center_L - FCSG_X_L / (scale / 100) - 5 * lineEndScale, -FCSG_Y_L / (scale / 100), center_L - FCSG_X_L / (scale / 100) + 5 * lineEndScale, -FCSG_Y_L / (scale / 100));
                } else if (groupL == 3 || groupL == 4) {   //Progressive or degressive
                    //Segmentbezugspunkt
                    pixi.graphics.lineStyle(yellowLinesWidth, pixiApi.colors.yellow);
                    pixi.graphics.drawLine(center_L - FCSG_X_L / (scale / 100), -FCSG_Y_L / (scale / 100) - 5 * lineEndScale, center_L - FCSG_X_L / (scale / 100), -FCSG_Y_L / (scale / 100) + 5 * lineEndScale);
                    pixi.graphics.drawLine(center_L - FCSG_X_L / (scale / 100) - 5 * lineEndScale, -FCSG_Y_L / (scale / 100), center_L - FCSG_X_L / (scale / 100) + 5 * lineEndScale, -FCSG_Y_L / (scale / 100));

                    //optische Zentrum
                    pixi.graphics.beginFill(pixiApi.colors.yellow)
                    pixi.graphics.drawEllipse(center_L - FCOC_X_L / (scale / 100), -FCOC_Y_L / (scale / 100), 2, 2);

                    //Fernbezugspunkt
                    pixi.graphics.arc(center_L - (FCSG_X_L - LRP_X_L + DRP_X_L) / (scale / 100), - (FCSG_Y_L - LRP_Y_L + DRP_Y_L) / (scale / 100), 25 * (25 / scale), -((300 / 180) * Math.PI), -((240 / 180) * Math.PI), true);

                    //Nahbezugspunkt
                    pixi.graphics.arc(center_L - (FCSG_X_L + LRP_X_L - NRPMAX_X_L) / (scale / 100), - (FCSG_Y_L - LRP_Y_L + NRPMAX_Y_L) / (scale / 100), 3 / (scale / 100), -Math.PI / 2, -Math.PI, true);
                    pixi.graphics.arc(center_L - (FCSG_X_L + LRP_X_L - NRPMIN_X_L) / (scale / 100), - (FCSG_Y_L - LRP_Y_L + NRPMIN_Y_L) / (scale / 100), 3 / (scale / 100), 0, -Math.PI / 2, true);
                    pixi.graphics.arc(center_L - (FCSG_X_L + LRP_X_L - NRPMIN_X_L) / (scale / 100), - (FCSG_Y_L - LRP_Y_L + NRPMIN_Y_L) / (scale / 100), 3 / (scale / 100), Math.PI / 2, 0, true);
                    pixi.graphics.arc(center_L - (FCSG_X_L + LRP_X_L - NRPMAX_X_L) / (scale / 100), - (FCSG_Y_L - LRP_Y_L + NRPMAX_Y_L) / (scale / 100), 3 / (scale / 100), -Math.PI, Math.PI / 2, true);
                }

                // let decX = (Math.round(decX_L*10) / 10)
                // let decY = (Math.round(decY_L*10) / 10)
                // formAndGeometry.left.decentrationX = isNaN(decX) ? 0 : decX
                // formAndGeometry.left.decentrationY = isNaN(decY) ? 0 : decY

                // if ('<?php echo $decentType; ?>' == "INTL") {
                //     document.getElementById('decX_L').value = document.getElementById('decX_L').value * -1;
                // }
            }

            //Blank
            pixi.graphics.lineStyle(1, pixiApi.colors.red);
            var diaml = minDiamL / (scale / 100);
            var decX_LScale = (FCBC_X_L) / (scale / 100);
            var decY_LScale = (FCBC_Y_L) / (scale / 100);

            pixi.graphics.beginFill(pixiApi.colors.red)
            pixi.graphics.drawEllipse(center_L + decX_LScale, - decY_LScale, 2, 2);
            pixi.graphics.drawEllipse(center_L + decX_LScale, - decY_LScale, diaml/2, diaml/2);

            //Drill
            let side = 'left'
            self.formAndGeometry.drillHoles.map(drillHole => {
                if (drillHole[side].startX && drillHole[side].startY) {
                    if (isNaN(drillHole[side].diam || !drillHole[side].diam)) {
                        drillHole.right.diam = "1.00"
                        drillHole.left.diam = "1.00"
                    }

                    var drillsX1Rnew = this.calcDrill(drillHole[side].direction, drillHole[side].startX, drillHole[side].startY, arrX_L, arrY_L, scale)
                    var drilleX1Rnew = this.calcDrill(drillHole[side].direction, drillHole[side].endX, drillHole[side].endY, arrX_L, arrY_L, scale)

                    if (drillsX1Rnew == 0 || drilleX1Rnew == 0) {
                        store.dispatch('notify', {
                            message: 'Values out of delivery range'
                        })
                        drillHole[side].startX = null
                        drillHole[side].startY = null
                        drillHole[side].endX = null
                        drillHole[side].endY = null
                    }
                    else {
                        this.drawDrill(
                            pixi.graphics,
                            drillsX1Rnew,
                            drillHole[side].startY,
                            drilleX1Rnew,
                            drillHole[side].endY,
                            drillHole[side].diam,
                            scale,
                        )
                    }
                }
            })
        }

        return pixi
    },

    /**
     * Draws the drill points inside of the Form and Geometry component
     */
    drawDrill(graphics, drillsX, drillsY, drilleX, drilleY, drilldi, scale) {
        if (!isNaN(drilleX) && !isNaN(drilleY) && (drillsX != drilleX || drillsY != drilleY)) {
            //zeichnen
            graphics.lineStyle({
                width: drilldi / (scale / 100),
                color: pixiApi.colors.red,
                cap: pixiApi.roundCap
            });
            graphics.drawLine(drillsX, -drillsY / (scale / 100), drilleX, -drilleY / (scale / 100));
        } else {
            // zeichnen
            graphics.beginFill(pixiApi.colors.red)
            const r = graphics.arc(drillsX, -drillsY / (scale / 100), drilldi / (scale / 100) / 2, 0, Math.PI * 2);
        }
    },

    /**
     * Calculates the position of a drill point. Used in drawDrill method
     */
    calcDrill(direction, drillX, drillY, arrX, arrY, scale) {
        var drillXreturn = 0;
        var drillXscale = parseFloat(drillX / (scale / 100));
        var drillYscale = parseFloat(drillY / (scale / 100));

        for (var i = 0; i < arrX.length - 1; i++) {
            if (arrY[i] <= -drillYscale && arrY[i + 1] >= -drillYscale && direction == "T") { //Temporal
                drillXreturn = parseFloat(arrX[i] + drillXscale);
            }
            if (arrY[i] >= -drillYscale && arrY[i + 1] <= -drillYscale && direction == "N") { //Nasal
                drillXreturn = parseFloat(arrX[i] - drillXscale);
            }
        }
        return drillXreturn;
    }
}
