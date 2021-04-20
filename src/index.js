import React from "react";
import ReactDOM from "react-dom";



const WCFM_Feature = () => {
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
            <FeatureVendorAdd featured_dates={featured_dates} _nonce={state.nonce_vendor_featured} />
            <FeaturedProducts products={vendor_products} />
            <FeaturedProductsAdd category_dates={category_dates} products={session_products} _nonce={state.nonce_featured_products} />
        </React.Fragment>
    )
};

const root_holder = document.getElementById("wc-multivendor-featured");
if (root_holder) {
    ReactDOM.render(<WCFM_Feature />, root_holder);
}