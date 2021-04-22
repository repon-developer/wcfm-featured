(function ($) {
    $(".wc-multivendor-featured-daftepicker").flatpickr({
        minDate: 'today'
    });



})(jQuery);

const { useState, useEffect, useRef } = React;
const { categories, products, unavailable_dates_vendor, product_limit } = wcfeatured;

const main_category = categories.filter(cat => cat.parent == 0);

const get_sub_categories = (parent) => {
    return parent && parent > 0 ? categories.filter(cat => cat.parent == parent) : [];
}

const Categories = ({ name, value, childof, onChange }) => {
    const on_update = (e) => {
        if (typeof onChange === 'function') {
            onChange(e.target.value)
        }
    }

    let categories = get_sub_categories(childof);
    if ( childof === undefined ) {
        categories = main_category
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
    const [pricing, setPricing] = useState({
        'home_page': 50.00,
        'category': 25.00,
        'subcategory': 15.00,
    });

    const [state, setState] = useState({
        category: 95,
        sub_category: null,
        packages: ['home_page', 'category']
    })

    const {category, sub_category, packages} = state;


    const on_submit = (e) => {
        if ( typeof props.onSubmit === 'function') {
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

        setState({...state, packages})
    }
    
    const dates = Array.isArray(props.dates) ? props.dates : [];

    const price = (packages.length ? packages.map((key) => pricing[key]).reduce((total, current) => total + current) : 0);
    const processing_fee = price * 5 / 100;

    const total_price = (price + processing_fee) * dates.length

    const date_strings = (dates) => {
        return dates.map(date => moment(date).format('MMM DD, YYYY')).join(', ');       
    }

    return (
        <table className="wcfm-feature-pricing">
            <tr>
                <th colSpan={3}>Select the pages where your job will be featured {dates.length > 0 && `(${date_strings(dates)})`}</th>
            </tr>

            <tr>
                <td className="checkbox-cell"><input type="checkbox" onChange={() => on_checkbox_update('home_page')} defaultChecked={packages.includes('home_page')} /></td>
                <td>Home Page</td>
                <td>{pricing.home_page.toFixed(2)} USD</td>
            </tr>

            <tr>
                <td className="checkbox-cell"><input type="checkbox" onChange={() => on_checkbox_update('category')} defaultChecked={packages.includes('category')} /></td>
                <td>Category Page <Categories value={category} name="category" onChange={(category) => setState({...state, category})} /></td>
                <td>{pricing.category.toFixed(2)} USD</td>
            </tr>

            <tr>
                <td className="checkbox-cell"><input type="checkbox" onChange={() => on_checkbox_update('subcategory')} defaultChecked={packages.includes('subcategory')} /></td>
                <td>
                    Subcategory Page 
                    {packages.includes('subcategory') && <Categories childof={category} value={sub_category} name="sub_category" onChange={(sub_category) => setState({...state, sub_category})} />}
                </td>
                <td>{pricing.subcategory.toFixed(2)} USD</td>
            </tr>

            <tr class="proccessing-fee">
                <td colSpan={2}>Processing Fee (5%)</td>
                <td>{processing_fee.toFixed(2)} USD</td>
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

    const featured_dates = Array.isArray(props.featured_dates) ? props.featured_dates : [];

    const [state, setState] = useState({
        category: '',
        dates: [],
    });

    const { dates, category } = state;

    useEffect(() => {
        const disable_dates = unavailable_dates_vendor.filter((date) => category == date.term_id).map((date) => date.feature_date);

        featured_dates.forEach((date) => {
            if (category === date.term_id) {
                disable_dates.push(date.feature_date)
            }
        });

        jQuery(datepicker.current).flatpickr({
            minDate: 'today',
            mode: "multiple",
            dateFormat: "Y-m-d",
            defaultDate: state.dates,
            disable: disable_dates,
            onChange: (selectedDates, dates, instance) => {
                dates = dates.split(',').map((date) => date.trim());
                setState({ ...state, dates })
            }
        })
    }, [category]);

    const onSubmit = (e) => {
        if (!category.length) {
            e.preventDefault();
            return alert('Please select a category');
        }

        if (dates.length == 0) {
            e.preventDefault();
            return alert('You have not selected any date.');
        }


        const _flatpickr = datepicker.current._flatpickr
        if (_flatpickr.selectedDates.length !== dates.length) {
            e.preventDefault();
            return alert('Please review selected dates again.')
        }
    }

    const price = Array.isArray(dates) ? (dates.length * wcfeatured.pricing.vendor).toFixed(2) : 0;

    return (
        <React.Fragment>
            {/* {featured_dates.length > 0 && <VendorFeaturedInfo categories={categories} />} */}
            <div className="wcfm-container" style={{ marginBottom: 40 }}>
                <div className="wcfm-content">
                    <h2>Feature your BLEX store</h2>
                    <div className="gap-10" />
                    <form className="wcfm-vendor-featured-form wcfm-vendor-featured-store-form" method="POST">
                        <input type="hidden" name="_nonce_featured_vendor" value={props._nonce} />

                        <fieldset className="wcfm-vendor-featured-fieldset wcfm-vendor-featured-fieldset-grid">
                            <label>Category</label>
                            <Categories name="feature_category" category={category} onChange={(category) => setState({ ...state, category })} />

                            <label>Date</label>
                            <input ref={datepicker} type="text" className="wcfm-text" />

                            {(Array.isArray(dates) && dates.length > 0) &&
                                <React.Fragment>
                                    {dates.map(date => <input type="hidden" name="feature_dates[]" value={date} />)}
                                    <label>Days</label>
                                    <span>{dates.length}</span>

                                    <label>Total Price</label>
                                    <span>${price}</span>
                                    <input type="hidden" name="price" value={price} />
                                </React.Fragment>
                            }

                        </fieldset>
                        <div className="gap-60" />
                        <button className="wcfm_submit_button" onClick={onSubmit}>Activate Now</button>
                    </form>
                </div>
            </div>
        </React.Fragment>
    )
}


const FeaturedProducts = (props) => {
    const products = Object.values(props.products);

    const date_string = (dates) => {
        return dates.map(date => moment(date).format('DD MMM'))
    }

    return (
        <table class="table-featured-products">
            <caption>Your Featured Products</caption>
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Sub Category</th>
                    <th>Dates</th>
                </tr>
            </thead>

            {products.length == 0 &&
                <tr>
                    <td colSpan={5} style={{ textAlign: 'center' }}>No Products</td>
                </tr>
            }

            {products.length > 0 && products.map((product) =>
                <tr>
                    <td>#{product.id}</td>
                    <td>{product.post_title}</td>
                    <td>{product.category_name}</td>
                    <td>{product.sub_category_name}</td>
                    <td>{date_string(product.dates).join(' | ')}</td>
                </tr>
            )}

        </table>
    );
}


const FeaturedProductForm = (props) => {
    const [product, setProduct] = useState({
        category: 80,
        dates: []
    });

    const datepicker = useRef(null);

    const { id, category, sub_category, dates } = product;



    useEffect(() => {
        //setProduct({...props.session_product})

    }, [props.session_product]);

    useEffect(() => {
        let term_id = product.sub_category && product.sub_category.length ? product.sub_category : product.category;
        const disable_dates = props.category_dates.filter((item) => item.term_id == term_id & item.total >= wcfeatured.product_limit).map(date => date.feature_date)

        const picker = flatpickr(datepicker.current, {
            minDate: 'today',
            mode: "multiple",
            dateFormat: "Y-m-d",
            defaultDate: dates,
            disable: disable_dates,
            onChange: (selectedDates, datesStr) => {
                datesStr = datesStr.split(',').map((date) => date.trim());
                setProduct({ ...product, dates: datesStr })
            }
        })

    }, [id, category, sub_category]);


    const on_submit = (e) => {
        let error = null;

        if (!id) {
            error = 'Please select a product';
        }

        if (!Array.isArray(dates) || !dates.length) {
            error = 'Please select feature dates';
        }

        if (!category) {
            error = 'Please select a category';
        }

        if (error) {
            e.preventDefault();
            return alert(error)
        }
    }


    const childs = get_sub_categories(product.category);

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
                                        return <option value={product.ID}>{product.post_title}</option>
                                    })}
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th>Date</th>
                            <td>
                                <input ref={datepicker} type="text" className="wcfm-text" />
                                { Array.isArray(dates) && dates.map((date) => <input type="hidden" name="dates[]" value={date} />)}
                            </td>
                        </tr>
                    </table>

                    <PricingPackage onSubmit={on_submit} dates={dates} />
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

    const { featured_dates, category_dates, vendor_products, session_products } = state;


    return (
        <React.Fragment>
            
            {/* <FeatureVendorAdd featured_dates={featured_dates} _nonce={state.nonce_vendor_featured} />
            <FeaturedProducts products={vendor_products} /> */}
            <FeaturedProductForm category_dates={category_dates} products={session_products} _nonce={state.nonce_featured_products} />
            
        </React.Fragment>
    )
}

const root_holder = document.getElementById("wc-multivendor-featured");
if (root_holder) {
    ReactDOM.render(<MultivendorFeatured />, root_holder);
}
