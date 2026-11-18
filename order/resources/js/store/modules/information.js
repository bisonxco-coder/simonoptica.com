import ApiService from '@/utils/api';

export default {
    state: {},
    getters: {},
    mutations: {},
    actions: {
        getProductCategoryInitial(context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/product-category-initial', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getProductCategoryUpdate(context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/product-category-update', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getDefaultMinThk(context, data) {
            data = ApiService.addAuthParameters(data);
            return new Promise((resolve, reject) => {
                ApiService.post('soap/default-min-thk', data)
                .then(({data}) => {
                  resolve(data);
                })
                .catch(({response}) => {
                  reject(response);
                });
            });
        },
        interpolate (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/interpolate', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getShapesList (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/shapes', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        /*getIndividualIni (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/individual-ini', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },*/
        getLensFilter (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/lens-filter', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getSalesFramesSupplierList (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/sales-frames-supplier-list', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getSalesFramesList (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/sales-frames-list', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getSalesShapesList (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/sales-shapes-list', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getLensTypes (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/lens-types', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getLensTypesOnly (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/lens-types-only', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getFramesSupplierList (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/frame-supplier', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getRangesCombination (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/ranges-combination', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getOverviewOrderSheet (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/overview-order-sheet', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getOrderbasketOrderSheet (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/orderbasket-order-sheet', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getFramePic (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/frame-pic', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getFrameByID (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/frame-by-id', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getLensGeo (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/lens-geo', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        convertDrillHoles (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/convert-drill-holes', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        transformDrillHoles (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/transform-drill-holes', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        scaleShape (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/scale-shape', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getCalculation (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/calculation', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        decryptTracedata (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/decrypt-trace-data', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getRemarkKey (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/remark-key', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getStockLensesByProdList (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/stock-lenses-by-prod-list', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getStockLensesWholeSale (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/stock-lenses-whole-sale', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getCustomerListWholeSale (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/customer-list-whole-sale', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getProdListsWholeSale (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/prod-lists-whole-sale', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getStockItems (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/stock-items', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getShopList (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/shop-list', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getCustomerDetails (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/shop-customer-details', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        createCustomer (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/create-customer', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        changeCustomerDetails (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/change-customer-details', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        deleteCustomer (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('soap/delete-customer', data)
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
