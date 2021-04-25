const { useState, useEffect, useRef } = React;
const { categories, products, vendor_filled_dates, products_filled_dates, product_limit } = wcfeatured;

const main_category = categories.filter(cat => cat.parent == 0);

const get_sub_categories = (parent) => {
    return parent && parent > 0 ? categories.filter(cat => cat.parent == parent) : [];
}

const Categories = ({ name, value, childof, onChange, product_cats }) => {
    const on_update = (e) => {
        if (typeof onChange === 'function') {
            onChange(e.target.value)
        }
    }

    let categories = get_sub_categories(childof);
    if (childof === undefined) {
        categories = main_category
    }

    if ( Array.isArray(product_cats) ) {
        categories = categories.filter((cat) => product_cats.includes(cat.term_id) );
    }

    return (
        <select name={name} defaultValue={value} className="wcfm-select" onChange={on_update}>
            <option value="">Select a category</option>
            {categories.map((cat) => {
                return <option className="level-0" value={cat.term_id}>{cat.name}</option>
            })}
        </select>
    )
}

const PricingPackage = (props) => {
    const pricing_id = props.pricing_id ? props.pricing_id : Date.now();
    const pricing = Object.assign({
        home_page: {
            price: 45.00,
            title: 'Home Page'
        },
        category: {
            price: 25.00,
            title: 'Category Page'
        },
        subcategory: {
            price: 15.00,
            hide: false,
            title: 'Subcategory Page'
        },
    }, props.pricing);

    Object.keys(pricing).forEach((key) => pricing[key].price = parseFloat(pricing[key].price));

    const { id, category, subcategory, packages } = Object.assign({ category: '', subcategory: '', packages: [] }, props.package);

    const on_submit = (e) => {
        if (typeof props.onSubmit === 'function') {
            props.onSubmit(e);
        }
    }

    const on_checkbox_update = (current) => {
        const index = packages.findIndex((pack) => pack === current)
        if (index >= 0) {
            packages.splice(index, 1);
        } else {
            packages.push(current)
        }

        props.onUpdate({ packages })
    }

    const dates = Array.isArray(props.dates) ? props.dates : [];

    const price = (packages.length ? packages.map((key) => pricing[key].price).reduce((total, current) => total + current) : 0);
    const processing_fee = price * 5 / 100;

    const total_price = (price + processing_fee) * dates.length

    const date_strings = (dates) => {
        return dates.map(date => moment(date).format('MMM DD, YYYY')).join(', ');
    }

    let product_cats = false;
    const product = products.find((product) =>{
        return product.id == id;
    });

    if (typeof product !== 'undefined' ) {
        product_cats = product.product_cats;
    }

    return (
        <table className="wcfm-feature-pricing">
            <tr>
                <th colSpan={3}>Select the pages where your job will be featured {dates.length > 0 && `(${date_strings(dates)})`}</th>
            </tr>

            <tr>
                <td className="checkbox-cell">
                    <input id={`${pricing_id}check_home_page`} name="packages[]" value="home_page" type="checkbox" onChange={() => on_checkbox_update('home_page')} defaultChecked={packages.includes('home_page')} />
                </td>
                <td><label for={`${pricing_id}check_home_page`}>{pricing.home_page.title}</label></td>
                <td>{pricing.home_page.price.toFixed(2)} x {dates.length} = {(pricing.home_page.price * dates.length).toFixed(2)} USD</td>
            </tr>

            <tr>
                <td className="checkbox-cell"><input id={`${pricing_id}check_category`} name="packages[]" value="category" type="checkbox" onChange={() => on_checkbox_update('category')} defaultChecked={packages.includes('category')} /></td>
                <td><label for={`${pricing_id}check_category`}>{pricing.category.title}</label> <Categories product_cats={product_cats} value={category} name="category" onChange={(category) => props.onUpdate({ category })} /></td>
                <td>{pricing.category.price.toFixed(2)} x {dates.length} = {(pricing.category.price * dates.length).toFixed(2)} USD</td>

            </tr>

            {pricing.subcategory.hide !== true &&
                <tr>
                    <td className="checkbox-cell"><input id={`${pricing_id}check_subcategory`} name="packages[]" value="subcategory" type="checkbox" onChange={() => on_checkbox_update('subcategory')} defaultChecked={packages.includes('subcategory')} /></td>
                    <td>
                        <label for={`${pricing_id}check_subcategory`}>{pricing.subcategory.title}</label>
                        {packages.includes('subcategory') && <Categories product_cats={product_cats} childof={category} value={subcategory} name="subcategory" onChange={(subcategory) => props.onUpdate({ subcategory })} />}
                    </td>
                    <td>{pricing.subcategory.price.toFixed(2)} x {dates.length} = {(pricing.subcategory.price * dates.length).toFixed(2)} USD</td>
                </tr>
            }

            <tr class="proccessing-fee">
                <td colSpan={2}>Processing Fee (5%)</td>
                <td>{processing_fee.toFixed(2)} x {dates.length} = {(processing_fee * dates.length).toFixed(2)} USD</td>
            </tr>

            {total_price > 0 &&
                <tfoot>
                    <tr>
                        <td colSpan={2}><button onClick={on_submit} className="wcfm_submit_button">Activate Now</button></td>
                        <td>Total {total_price.toFixed(2)} USD <input name="price" type="hidden" value={total_price.toFixed(2)} /></td>
                    </tr>
                </tfoot>
            }
        </table>
    );
}


const FeatureVendorAdd = (props) => {
    const datepicker = useRef(null);
    const vendor_prices = wcfeatured.pricing.vendor;

    const pricing = {
        home_page: {
            title: 'Blex store homepage',
            price: vendor_prices.home_page || 50.00,
        },

        category: {
            title: 'Category Page',
            price: vendor_prices.category || 30.00
        },
        subcategory: {
            hide: true,
            title: 'Subcategory Page',
            price: 0
        },
    }

    const [state, setState] = useState({
        category: '',
        subcategory: '',
        dates: [],
        packages: ['home_page', 'category']
    });

    const { dates, category, subcategory, packages } = state;

    useEffect(() => {
        jQuery(datepicker.current).flatpickr({
            minDate: 'today',
            mode: "multiple",
            dateFormat: "Y-m-d",
            defaultDate: state.dates,
            onChange: (selectedDates, dates, instance) => {
                dates = dates.split(',').map((date) => date.trim());
                setState({ ...state, dates })
            }
        })

    }, [category, subcategory]);

    const on_update = (values) => setState({ ...state, ...values })

    const onSubmit = (e) => {
        let error = null;

        if (!Array.isArray(dates) || !dates.length) {
            error = 'Please select feature dates';
        }

        if (packages.includes('category') && !category) {
            error = 'Please select a category';
        }

        if (packages.includes('subcategory') && !subcategory) {
            error = 'Please select a subcategory';
        }

        if (error) {
            e.preventDefault();
            return alert(error)
        }

        const _flatpickr = datepicker.current._flatpickr
        if (_flatpickr.selectedDates.length !== dates.length) {
            e.preventDefault();
            return alert('Please review selected dates again. Some dates is not available for featuring your BLEX store.')
        }
    }

    return (
        <React.Fragment>
            <div className="wcfm-container" style={{ marginBottom: 40 }}>
                <div className="wcfm-content">
                    <h2>Feature your BLEX store</h2>
                    <div className="gap-10" />
                    <form className="wcfm-vendor-featured-form wcfm-vendor-featured-store-form" method="POST">
                        <input type="hidden" name="_nonce_featured_vendor" value={props._nonce} />

                        <table className="table-wcfm-form">
                            <tr>
                                <th>Date</th>
                                <td>
                                    <input ref={datepicker} type="text" className="wcfm-text" />
                                    {Array.isArray(dates) && dates.map((date) => <input type="hidden" name="dates[]" value={date} />)}
                                </td>
                            </tr>
                        </table>

                        <PricingPackage pricing_id="vendor" pricing={pricing} package={state} onSubmit={onSubmit} onUpdate={on_update} dates={dates} />
                    </form>
                </div>
            </div>
        </React.Fragment>
    )
}


const FeaturedDates = (props) => {
    const products = Object.values(props.products);

    const get_package_string = (packages) => {
        return packages.map((pack) => {
            if (pack === 'home_page') {
                return 'Home';
            }

            if (pack === 'category') {
                return 'Category';
            }

            if (pack === 'subcategory') {
                return 'Sub Category';
            }
        })
    }

    return (
        <table class="table-featured-products">
            <caption>Your Featured Products</caption>
            <thead>
                <tr>
                    <th>Dates</th>
                    <th>#ID</th>
                    <th>Name</th>
                    <th>Package</th>
                    <th>Category</th>
                    <th>Sub Category</th>
                </tr>
            </thead>

            {products.length == 0 &&
                <tr>
                    <td colSpan={5} style={{ textAlign: 'center' }}>No Products</td>
                </tr>
            }

            {products.length > 0 && products.map((product) =>
                <tr>
                    <td>{moment(product.date).format('MMM DD, YYYY')}</td>
                    <td>#{product.id}</td>
                    <td>{product.post_title}</td>
                    <td>{get_package_string(product.packages).join(', ')}</td>
                    <td>{product.packages.includes('category') || product.packages.includes('subcategory') ? product.category_name : ''}</td>
                    <td>{product.subcategory_name}</td>
                </tr>
            )}

        </table>
    );
}

const FeaturedProductForm = (props) => {
    const product_prices = wcfeatured.pricing.product;

    const pricing = {
        home_page: {
            title: 'Home Page',
            price: product_prices.home_page || 50.00,
        },

        category: {
            title: 'Category Page',
            price: product_prices.category || 30.00
        },
        subcategory: {
            title: 'Subcategory Page',
            price: product_prices.subcategory || 20.00
        },
    }

    const [product, setProduct] = useState({
        id: null,
        dates: [],
        category: '',
        subcategory: null,
        packages: ['home_page', 'category']
    });

    const { id, category, subcategory, dates, packages } = product;

    const datepicker = useRef(null);

    useEffect(() => {
        flatpickr(datepicker.current, {
            minDate: moment().add(1,'days').format(),
            mode: "multiple",
            dateFormat: "Y-m-d",
            defaultDate: dates,
            onChange: (selectedDates, datesStr) => {
                datesStr = datesStr.split(',').map((date) => date.trim());
                if (!selectedDates.length) {
                    datesStr = [];
                }
                setProduct({ ...product, dates: datesStr })
            }
        })

    }, [id, category, subcategory]);

    const on_update = (values) => setProduct({ ...product, ...values })


    const on_submit = (e) => {
        let error = null;

        if (!id) {
            error = 'Please select a product';
        }

        if (!Array.isArray(dates) || !dates.length) {
            error = 'Please select feature dates';
        }

        if (packages.includes('category') && !category) {
            error = 'Please select a category';
        }

        if (packages.includes('subcategory') && !subcategory) {
            error = 'Please select a subcategory';
        }

        if (error) {
            e.preventDefault();
            return alert(error)
        }
    }

    return (
        <div className="wcfm-container">
            <div className="wcfm-content">
                <h2>Feature your Products</h2>
                <div className="gap-20" />
                <form className="wcfm-vendor-featured-form wcfm-vendor-featured-product-form" method="post">
                    <input type="hidden" name="_nonce_featured_products" value={props._nonce} />
                    <div className="wcfm_clearfix" />

                    <table className="table-wcfm-form">
                        <tr>
                            <th>Product</th>
                            <td>
                                <select defaultValue={id} name="id" className="wcfm-select" onChange={(e) => setProduct({ ...product, id: e.target.value })} >
                                    <option value="">Select a product</option>
                                    {Array.isArray(products) && products.map((product) => {
                                        return <option value={product.id}>{product.post_title}</option>
                                    })}
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th>Date</th>
                            <td>
                                <input ref={datepicker} type="text" className="wcfm-text" />
                                {Array.isArray(dates) && dates.map((date) => <input type="hidden" name="dates[]" value={date} />)}
                            </td>
                        </tr>
                    </table>

                    <PricingPackage pricing={pricing} package={product} onSubmit={on_submit} onUpdate={on_update} dates={dates} />
                </form>
            </div>
        </div>
    )
}

const MultivendorFeatured = () => {
    const [state, setState] = useState({
        status: 'loading'
    });

    useEffect(() => {
        jQuery.post(wcfeatured.ajax, { action: "get_featured_data" }, function (data) {
            setState({ ...data });

        }).fail(() => {
            alert('Something wrong.')
        })
    }, [])

    if (state.status === 'loading') {
        return <h2 className="loading">Loading...</h2>
    }

    const { nonce_vendor_featured, nonce_featured_products, featured_products } = state;

    return (
        <React.Fragment>
            <FeatureVendorAdd _nonce={nonce_vendor_featured} />
            <FeaturedDates products={featured_products} />
            <FeaturedProductForm _nonce={nonce_featured_products} />
        </React.Fragment>
    )
}

const root_holder = document.getElementById("wc-multivendor-featured");
if (root_holder) {
    ReactDOM.render(<MultivendorFeatured />, root_holder);
}
