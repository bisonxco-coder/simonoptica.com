import store from '@/store'

const self = {
    get orders() {return store.state.moduleOrders},
    get individual() { return store.state.moduleOrders.details.individual},
    get individualProperties() { return store.state.moduleEvaluator.details.individual},
}
export default {
    doDefinedFieldsHaveValues(definedFields) {
        if (!definedFields || definedFields == undefined || definedFields.length == 0) return false
        return true
    },
    async translateIndividualString(side, indtype, individualstring, lenscoder = '', lenscodel = '') {
      let individual = {
            definedFields: [],
            right: {
                type: null,
                definedFields: [],
            },
            left: {
                type: null,
                definedFields: [],
            }
        }

        let right = []
        let left = []
        let common = []

      self.orders.isLoading = true
        return await store.dispatch('translateIndividual', {
            side: side,
            indtype: indtype,
            individualstring: individualstring,
            lenscoder: lenscoder,
            lenscodel: lenscodel,
        }).then((response) => {
            self.orders.isLoading = false
            try {

              if(response['indi']) {
              if (response['indi'].Ranges[0]) {
                if (response['indi'].Ranges[0]['INDIVIDUALDETAIL']) {
                  const indMap = response['indi'].Ranges[0]['INDIVIDUALDETAIL']
                  let right = []
                  let left = []
                  let common = []
                  for (let key in indMap['PARAMETER']) {
                    let indKey = indMap['PARAMETER'][key]
                    if (indMap[indKey]) {
                      let s = {
                        name: indMap['PARAMETER'][key],
                        get label() {
                            if(indMap[indKey]['DESCRIPTION'] && indMap[indKey]['DESCRIPTION'] !== '')
                                return indMap[indKey]['DESCRIPTION']
                            return store.getters.translations.newOrder.details.individual.placeholders[indMap['PARAMETER'][key]]
                        },
                        pos: indMap[indKey]['POS'],
                        required: indMap[indKey]['REQUIRED'],
                      }
                      if (indMap[indKey]['MIN']) {
                        s['min'] = indMap[indKey]['MIN']
                        s['max'] = indMap[indKey]['MAX']
                        s['step'] = 0.1
                        s['decimals'] = 2
                      } else {
                        s['validChars'] = indMap[indKey]['VALIDCHARS']
                      }
                      if (indMap[indKey]['RELATION'] === 'S') {
                        right.push(s)
                        left.push(s)
                      } else {
                        common.push(s)
                      }
                    }
                    self.orders.details.individual.fieldMaps.right = right
                    self.orders.details.individual.fieldMaps.left = left
                    self.orders.details.individual.fieldMaps.common = common
                  }
                }
              }
            }

                // Key-pair data where key is the param property of the individual field
                let individualData = response['translated']
                if (!individualData || individualData === undefined) return

                const individualfieldMaps = self.individual.fieldMaps

                Object.entries(individualData).forEach(individualEntry => {
                    let fieldName = individualEntry[0]
                    let fieldValue = individualEntry[1]
                    if (!fieldValue || fieldValue == '')
                        return

                    const sideFieldMap = individualfieldMaps[side].find(x => x.name == fieldName)
                    if (sideFieldMap && sideFieldMap !== undefined) {
                        individual[side].type = indtype
                        individual[side].definedFields.push({
                            name: fieldName,
                            label: sideFieldMap.label,
                            value: fieldValue
                        })
                    }

                    const commonFieldMap = individualfieldMaps.common.find(x => x.name == fieldName)
                    let existingDefinedField = null
                    if (commonFieldMap && commonFieldMap !== undefined) {
                        if (individual.definedFields && individual.definedFields.length > 0){
                            existingDefinedField = individual.definedFields.find(x => x.name == fieldName)
                        }

                        if (existingDefinedField && existingDefinedField !== undefined) {
                            existingDefinedField.value = fieldValue
                        }else {
                            individual.definedFields.push({
                                name: fieldName,
                                label: commonFieldMap.label,
                                value: fieldValue
                            })
                        }
                    }
                })
            } catch (error) {
                console.error(error)
            }

            return individual
        }, e => {
            self.orders.isLoading = false
            store.dispatch('notify', {
                message: e.data.message,
            })
            console.error(e)
        });
    },
}
