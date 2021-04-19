(function ($) {
    $(".wc-multivendor-featured-daftepicker").flatpickr({
        minDate: 'today'
    });



})(jQuery);

const { useState, useEffect, useRef } = React;
const { categories, products, unavailable_dates_vendor } = wcfeatured;

const main_category = categories.filter(cat => cat.parent == 0);

const get_sub_categories = (parent) => {
    return parent && parent > 0 ? categories.filter(cat => cat.parent == parent) : [];
}

const Categories = ({ name, category, onChange }) => {
    const on_update = (e) => {
        if (typeof onChange === 'function') {
            onChange(e.target.value)
        }
    }

    return (
        <select name={name} defaultValue={category} className="wcfm-select" onChange={on_update}>
            <option value="">Select a category</option>
            {main_category.map((cat) => {
                return <option className="level-0" value={cat.term_id}>{cat.name}</option>
            })}
        </select>
    )
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
            if ( category === date.term_id ) {
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



const ProductItem = (props) => {
    const { product } = props;

    const datepicker = useRef(null);

    useEffect(() => {
        jQuery(datepicker.current).flatpickr({ minDate: 'today' })

    }, [datepicker.current]);

    useEffect(() => {
        jQuery(datepicker.current).change(function (selectedDates, dateStr, instance) {
            if (typeof props.onChange === 'function') {
                props.onChange({ ...product, start: selectedDates.target.value })
            }
        })

    }, [props]);

    const update = (product) => {
        if (typeof props.onChange === 'function') {
            props.onChange(product)
        }
    }

    const on_delete = (index) => {
        if (typeof props.onDelete === 'function') {
            props.onDelete(index)
        }
    }

    const childs = get_sub_categories(product.category);

    return (
        <fieldset className="wcfm-vendor-featured-fieldset wcfm-vendor-featured-fieldset-grid wcfm-vendor-featured-fieldset-product-grid">
            <span className="btn-delete" onClick={() => on_delete(props.number)}>Delete</span>
            <label>Product</label>
            <select defaultValue={product.id} name={`featured_products[${props.number}][id]`} className="wcfm-select" onChange={(e) => update({ ...product, id: e.target.value })} >
                <option value="">Select a product</option>
                {Array.isArray(products) && products.map((product) => {
                    return <option value={product.ID}>{product.post_title}</option>
                })}
            </select>
            <label>Start Date</label>
            <input name={`featured_products[${props.number}][start]`} ref={datepicker} type="text" className="wcfm-text wc-multivendor-featured-datepicker" value={product.start} />
            <label>Days</label>
            <input type="text" name={`featured_products[${props.number}][days]`} className="wcfm-text" defaultValue={product.days} onChange={(e) => update({ ...product, days: e.target.value })} />
            <label>Category</label>
            <Categories name={`featured_products[${props.number}][category]`} category={product.category} onChange={(category) => update({ ...product, category })} />

            {childs.length > 0 &&
                <React.Fragment>
                    <label>Sub Category</label>
                    <select defaultValue={product.sub} className="wcfm-select" name={`featured_products[${props.number}][sub]`}>
                        <option value="">Select Sub Category</option>
                        {childs.map(c => <option value={c.term_id}>{c.name}</option>)}
                    </select>
                </React.Fragment>
            }
        </fieldset>
    )
}

const FeaturedProductsAdd = (props) => {
    const [products, setProducts] = useState(props.products);

    const add_feature_product = () => {
        products.push({ id: '', start: '', days: 1, category: '' });
        setProducts([...products])
    }

    const on_update = (product, i) => {
        products[i] = product;
        setProducts([...products])
    }

    const on_delete = (index) => {
        products.splice(index, 1);
        setProducts([...products])
    }

    const on_submit = (e) => {
        let error = null;

        if (!products.length) {
            e.preventDefault();
            alert('Please add a product.')
        }

        for (const key in products) {
            const product = products[key];

            if (!product.id) {
                error = 'Please select a product';
                continue;
            }

            if (!product.start) {
                error = 'Please select start date';
                continue;
            }

            if (!product.days) {
                error = 'Please specify duration';
                continue;
            }

            if (!product.category) {
                error = 'Please select a category';
                continue;
            }
        }

        const result = products.map(a => a.id);
        let findDuplicates = result.filter((item, index) => result.indexOf(item) != index)

        if (findDuplicates.length) {
            error = 'Please select unique product.';
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

                    {products.map((p, index) => <ProductItem onDelete={on_delete} key={index} number={index} product={products[index]} onChange={(product) => on_update(product, index)} />)}

                    <span className="add-new-btn" onClick={add_feature_product}>Add featured product</span>

                    <div className="gap-60" />
                    <button className="wcfm_submit_button" onClick={on_submit}>Activate Now</button>
                </form>
            </div>
        </div>
    )
}


const FeaturedProducts = (props) => {
    const products = Object.values(props.products)

    return (
        <table class="table-featured-products">
            <caption>Your Featured Products</caption>
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Name</th>
                    <th>Start Date</th>
                    <th>Expired on</th>
                    <th>Days</th>
                    <th>Category</th>
                </tr>
            </thead>

            {products.length == 0 &&
                <tr>
                    <td colSpan={7} style={{ textAlign: 'center' }}>No Products</td>
                </tr>
            }

            {products.length > 0 && products.map((product) =>
                <tr>
                    <td>#{product.id}</td>
                    <td>{product.post_title}</td>
                    <td>{moment(product.start).format('DD MMM, YYYY')}</td>
                    <td>{moment(product.start).add(product.days, 'days').format('DD MMM, YYYY')}</td>
                    <td>{product.days}</td>
                    <td>{product.term_name}</td>
                </tr>
            )}

        </table>
    );
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

    const { featured_dates, vendor_featured_products, session_products } = state;
    return (
        <React.Fragment>
            <FeatureVendorAdd featured_dates={featured_dates} _nonce={state.nonce_vendor_featured} />

            <FeaturedProducts products={vendor_featured_products} />
            <FeaturedProductsAdd products={session_products} _nonce={state.nonce_featured_products} />
        </React.Fragment>
    )
}

const root_holder = document.getElementById("wc-multivendor-featured");
if (root_holder) {
    ReactDOM.render(<MultivendorFeatured />, root_holder);
}
