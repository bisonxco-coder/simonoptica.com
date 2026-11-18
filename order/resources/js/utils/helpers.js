import RecType from '../JSON/RecType.json'
export default {
    // format from yyyy.mm.dd to dd.mm.yyyy
    fromYMDtoDMY (date) {
        let splitDate = date.split('-');
        if (splitDate.length > 1)
            return `${splitDate[2]}.${splitDate[1]}.${splitDate[0]}`
        return date
    },

    // Returns true if list is empty
    isListEmpty (list) { return list === undefined || list === null || list === '' || list.length === undefined || list.length === 0 },

    // Return the value of obj[prop] or empty, if obj[prop] does not exist
    getObjProp(obj, prop, empty) { return obj && obj[prop] !== undefined ? obj[prop] : (empty ? empty : '') },

    // Just like getObjProp but for zero value numbers
    getObjPropNumber(obj, prop, empty){
        try {
            const r = this.getObjProp(obj, prop, null)
            return (!r || isNaN(r) || r == 0) ? empty : r
        } catch (error) {}
        return empty
    },

    // Return parsed float value or null if empty
    parseOrNull (val) { return val ? parseFloat(val) : null },

    // Parse decimals (example from +1600 to 16.00)
    parseDecimal (val) { return parseFloat(val / 100).toFixed(2) },

    // Check if value falls in between min-max range
    inRange (val, min, max) { return min <= val && val <= max },

    // Push all unique min-max diameter values into array for use in slider range
    pushToRange (val, array) { if (!array.includes(val) && val > 0) array.push(val) },

    // Get decimal step (example 4: 1/4 = 0.25 steps are 1.00, 1.25, 1.50,...)
    toStep (val) {
        if (val == 9) {
            return 1 / 100
        } else {
            return 1 / val
        }
    },

    // Convert hex to ascii string
    hexToString(hex){
        var hexString = hex.toString();
        var str = '';
        for (var n = 0; n < hex.length; n += 2) {
            str += String.fromCharCode(parseInt(hexString.substr(n, 2), 16));
        }
        return str;
    },

    // Convert string to hex
    stringToHex(string){
        return string.split("").reduce((hex,c)=>hex+=c.charCodeAt(0).toString(16).padStart(2,"0"),"")
    },

    // Convert base64 to string
    base64ToString(string){
        return Buffer.from(string, 'base64').toString('ascii')
    },

    // Convert string to base64
    stringToBase64(string){
        return Buffer.from(string).toString('base64')
    },

    // Prevent calling the same function multiple times at once.
    // Used to prevent calling the same REST call multiple times.
    throttleDelay(timer, callback, delay = 100){
        if (timer)
            clearTimeout(timer)
        return setTimeout(() => callback(callback), delay)
    },

    // foo -> Foo
    capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    },

    // foo -> FOO
    capitalize(string) {
        return string.toUpperCase()
    },

    // Create a link from a base64 encoded string
    linkFromBase64(string) {
        return `data:application/pdf;base64,${string}`;
    },

    // Force the browser to download a file using a base64 string.
    downloadBase64File(contentBase64, fileName='print') {
        if (!contentBase64) return

        const linkSource = this.linkFromBase64(contentBase64)
        const downloadLink = document.createElement('a');
        document.body.appendChild(downloadLink);

        downloadLink.href = linkSource;
        downloadLink.target = '_self';
        downloadLink.download = fileName;
        downloadLink.click();
    },

    // Open a new browser tab and print a pdf using a fullscreen iframe
    printBase64File(contentBase64) {
        if (!contentBase64) return

        const linkSource = this.linkFromBase64(contentBase64)

        let pdfWindow = window.open("")
        pdfWindow.document.write(`
            <iframe width='100%' height='100%' src='${linkSource}'></iframe>
            <style>
                body {
                    margin: 0px;
                    overflow: hidden;
                }
            </style>
        `)
    },

    // Takes children objects of 'parentObject' that are called 'left' and 'right',
    // copies 'right' values to 'left' while excluding any properties contained
    // in 'excludedProperties' or taking only 'onlyProperties'.
    // Execute 'fieldSetCallback' for specific field handling
    copyDataFromRightToLeftSide(parentObject, onlyProperties = [], excludedProperties = [], fieldSetCallback = (field) => {}){
        let errorTitle = "Internal error while copying data: "

        if (!parentObject || Object.keys(parentObject).length == 0 || Array.isArray(parentObject))
            return errorTitle + "invalid component data type."

        try {
            let sides = ['left', 'right']
            let entries = Object.entries(parentObject).filter(x => sides.find(side => side == x[0]))
            let hasBothSides = entries.length == 2 ? true : false

            if (!hasBothSides)
                return errorTitle + "component data is missing either left or right side objects."

            let takeOnlyProperties = onlyProperties.length > 0 ? true : false
            Object.entries(parentObject.right).forEach(x => {
                let fieldName = x[0]
                if (takeOnlyProperties){
                    let found = onlyProperties.find(x => x == fieldName)
                    if(!found) return
                }
                else {
                    if(excludedProperties.find(x => x == fieldName)) return
                }
                parentObject.left[fieldName] = parentObject.right[fieldName]
                fieldSetCallback(fieldName)
            })

        } catch (error) {
            return error
        }

        return true
    },

    // Compare 2 objects. Third parameter allows excluding object properties
    areObjectsEqual (object1, object2, excludeChecking = []){
        if ((!object1 || object1 == undefined || Object.entries(object1) == 0) ||
            (!object2 || object2 == undefined || Object.entries(object2) == 0))
            return false

        let object1Clone = object1
        let object2Clone = object2

        // Optionally remove some properties to exclude from comparing
        if (excludeChecking.length > 0){
            object1Clone = JSON.parse(JSON.stringify(object1))
            object2Clone = JSON.parse(JSON.stringify(object2))
            try {
                excludeChecking.forEach(exclude => {
                    if (object1Clone[exclude] !== undefined)
                        delete object1Clone[exclude]

                    if (object2Clone[exclude] !== undefined)
                        delete object2Clone[exclude]
                })
            } catch (error) {}
        }

        let object1Json = JSON.stringify(object1Clone)
        let object2Json = JSON.stringify(object2Clone)
        return object1Json === object2Json
    },

    // Flatten all object to be like: 'product.right.selectedLensType'
    flattenObj(obj, parent, name, res = {}){
        for(let key in obj){
            let propName = parent ? parent + '.' + key : key;
            if(typeof obj[key] == 'object'){
                this.flattenObj(obj[key], propName, name, res);
            } else {
                if (key == name){
                    res[propName] = obj[key];
                }
            }
        }
        return res;
    },

    // Get the reference to the object, by its' property name inside a nested object
    getObjectProperties(object, propertyName) {
        let allFields = []
        const fields = this.flattenObj(object, null, propertyName)
        Object.entries(fields).forEach(field => {

            // Remove the last dot notation object to find the correct object name
            // ie. from details.refraction.left.addition.required -> details.refraction.left.addition
            let referenceName = field[0].split('.' + propertyName)[0]
            let reference = eval('object.' + referenceName)

            let refSplit = referenceName.split('.')
            let parent = null
            let parentName = ''
            if (refSplit.length == 1) {
                parentName = refSplit[0]
                parent = eval('object')
            }else {
                parentName = refSplit.filter(x => x !== refSplit.at(-1)).join('.')
                parent = eval('object.' + parentName)
            }

            // If the field has a function, execute it.
            let property = field[1]
            if (typeof field[1] == 'function'){
                property = field[1](reference)
            }

            // name: the original trailing name with the property ie. details.refraction.left.addition.required
            // value: the value of the property
            // referenceName: trailing name of reference object without the property ie. details.refraction.left.addition
            // reference: reference to the original object
            // parentName: trailing name of parent reference
            // parent: reference to the parent object of reference
            allFields.push({
                name: field[0],
                value: property,
                referenceName: referenceName,
                reference: reference,
                parentName: parentName,
                parent: parent,
            })
        })
        return allFields
    },

    // Takes UTC date time (date + time). Returns locally parsed date time
    parseUTCtoLocal(dateTime, addUTCCaption = true){
        const utcDate = new Date(dateTime.replace(' ', 'T') + "Z");
        const localOffset = utcDate.getTimezoneOffset();
        const localDate = new Date(utcDate.getTime() - localOffset * 60 * 1000);

        return localDate.toISOString().replace('T', ' ').slice(0, 19);
    },

    /**
     * Convert rgb to hex
     * @param {array} color tupple array for rgb, like [255,255,255]
     * @param {string} prefix
     * @returns {string}
     */
    RGBToHex(color, prefix = '0x') {
        return prefix + ((1 << 24) + (color[0] << 16) + (color[1] << 8) + color[2]).toString(16).slice(1);
    },

    // Take color string and convert to hex
    colorNameToHex(name, prefix = '0x') {
        let hash = 0;
        name.split('').forEach(char => {
            hash = char.charCodeAt(0) + ((hash << 5) - hash)
        })
        let colour = prefix
        for (let i = 0; i < 3; i++) {
            const value = (hash >> (i * 8)) & 0xff
            colour += value.toString(16).padStart(2, '0')
        }
        return colour
    },
    reduceArr(arr, val, recType, recTypeKey){
        return  arr.reduce((acc, d) => {
            const found = acc.find(a => a[val] === d[val]);
            const customRecType = this.getCustomRecType()
            if (!found) {
                try{
                    let i;
                    if (customRecType[recType] !== undefined && customRecType[recType])
                        i = customRecType[recType].find(x => x[recTypeKey] === d[val])
                    else
                        i = RecType[recType].find(x => x[recTypeKey] === d[val])
                    let array = {};
                    Object.keys(i).forEach(function(key) {
                        array[key] = i[key]
                    })
                    acc.push(array) // not found, so need to add data property
                }
                catch (error) {
                    console.log('Value ' + d[val] + ' not find in RecType')
                }
            }
            return acc;}, []);
    },
    getCustomRecType() {
        let result = []
        const customFiles =require.context('../JSON/custom', true, /[A-Za-z0-9-_,\s]+\.json$/i); //require.context('./custom', true, /[A-Za-z0-9-_,\s]+\.json$/i)
        customFiles.keys().forEach(async file => {
            result = customFiles(file)
        })
        return result
    }
}
