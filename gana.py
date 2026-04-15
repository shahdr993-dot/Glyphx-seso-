import streamlit as st
import folium
from streamlit_folium import st_folium
from geopy.geocoders import Nominatim
import requests

# 1. إعداد الصفحة (لازم أول سطر)
st.set_page_config(layout="wide", page_title="Egypt Luxe Guide", page_icon="🇪🇬")

# 2. كود الـ HTML و الـ CSS (الفيديو + الستايل الزجاجي)
st.markdown("""
    <style>
    /* فيديو الخلفية */
    #bg-video {
        position: fixed;
        right: 0; bottom: 0;
        min-width: 100%; min-height: 100%;
        z-index: -1;
        opacity: 0.3;
        object-fit: cover;
    }
    .stApp { background: transparent; }
    
    /* تصميم الكروت الزجاجية */
    .glass-header {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(15px);
        padding: 20px;
        border-radius: 20px;
        border: 1px solid rgba(212, 175, 55, 0.3);
        text-align: center;
        margin-bottom: 20px;
    }
    /* جعل السايدبار شفاف */
    [data-testid="stSidebar"] {
        background: rgba(26, 28, 35, 0.8) !important;
        backdrop-filter: blur(15px);
        border-right: 1px solid #d4af37;
    }
    </style>

    <video autoplay muted loop id="bg-video">
        <source src="https://assets.mixkit.co/videos/preview/mixkit-top-view-of-a-city-at-night-11001-large.mp4" type="video/mp4">
    </video>
    """, unsafe_allow_html=True)

# 3. محرك البحث عن الأماكن والطقس (The Engine)
def get_nearby_spots(lat, lon, category_key):
    url = "http://overpass-api.de/api/interpreter"
    query = f'[out:json];node["amenity"="{category_key}"](around:3000, {lat}, {lon});out 15;'
    try:
        r = requests.get(url, timeout=5)
        return r.json().get('elements', [])
    except: return []

def get_weather(city):
    weather_dict = {
        "Cairo": "22°C | صافي ☀️",
        "Luxor": "28°C | مشمس 🏜️",
        "Alexandria": "19°C | غائم ☁️",
        "Aswan": "30°C | حار ✨"
    }
    return weather_dict.get(city, "24°C | معتدل 🌤️")

# 4. السايدبار (Control Center)
with st.sidebar:
    st.markdown("<h1 style='color:#d4af37;'>⚜️ Egypt Guide</h1>", unsafe_allow_html=True)
    
    selected_city = st.selectbox("اختر مدينة مصرية:", ["Cairo", "Luxor", "Alexandria", "Aswan"])
    category_label = st.selectbox("أبحث عن:", ["مطاعم", "كافيهات", "فنادق", "متاحف"])
    
    cat_map = {"مطاعم": "restaurant", "كافيهات": "cafe", "فنادق": "hotel", "متاحف": "museum"}
    
    # واجهة الطقس
    weather = get_weather(selected_city)
    st.markdown(f"""
        <div style="background:rgba(212,175,55,0.1); padding:15px; border-radius:15px; border:1px solid #d4af37; text-align:center;">
            <p style="margin:0; color:white;">حالة الطقس الآن في {selected_city}</p>
            <h2 style="margin:5px 0; color:#d4af37;">{weather}</h2>
        </div>
    """, unsafe_allow_html=True)
    
    st.divider()
    if st.button("احجز رحلتك الآن ✈️"):
        st.balloons()
        st.success("تم تسجيل طلبك بنجاح!")

# 5. الجزء الرئيسي (البحث والخريطة)
st.markdown('<div class="glass-header"><h1>🇪🇬 LUXE EGYPT EXPLORER</h1></div>', unsafe_allow_html=True)

# منطق تحديد الإحداثيات
geolocator = Nominatim(user_agent="egy_travel_final")
loc = geolocator.geocode(selected_city)
coords = [loc.latitude, loc.longitude] if loc else [30.0444, 31.2357]

# بناء الخريطة
m = folium.Map(location=coords, zoom_start=13, tiles='CartoDB dark_matter')

# إضافة الأماكن القريبة أوتوماتيكياً
spots = get_nearby_spots(coords[0], coords[1], cat_map[category_label])
for spot in spots:
    name = spot.get('tags', {}).get('name', 'مكان مميز')
    folium.Marker(
        [spot['lat'], spot['lon']],
        popup=name,
        icon=folium.Icon(color='orange', icon='star')
    ).add_to(m)

# عرض الخريطة
st_folium(m, width="100%", height=600)