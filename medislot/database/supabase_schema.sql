-- ============================================================
-- MEDISLOT — Supabase PostgreSQL Schema
-- Project: PPL-Kell-E (PKE)
-- Covers: PKE-1 through PKE-17 backlog items
-- ============================================================

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- ============================================================
-- 1. PROFILES — PKE-1: Pengelolaan Profil
--    Extends Supabase auth.users with patient profile data
-- ============================================================
CREATE TABLE IF NOT EXISTS public.profiles (
    id          UUID PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
    name        TEXT NOT NULL,
    age         INTEGER NOT NULL CHECK (age > 0),
    gender      TEXT NOT NULL CHECK (gender IN ('Laki-laki', 'Perempuan', 'Lainnya')),
    phone       TEXT,
    address     TEXT,
    avatar_url  TEXT,
    role        TEXT NOT NULL DEFAULT 'pasien' CHECK (role IN ('pasien', 'admin')),
    created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE public.profiles IS 'PKE-1: Pengelolaan Profil — stores patient and admin profile data';

-- ============================================================
-- 2. HEALTH_DATA — PKE-2: Data Kesehatan Dasar
--    Stores baseline health metrics for a patient
-- ============================================================
CREATE TABLE IF NOT EXISTS public.health_data (
    id                          UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id                     UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    blood_type                  TEXT CHECK (blood_type IN ('A', 'B', 'AB', 'O', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-')),
    weight_kg                   DECIMAL(5,2) CHECK (weight_kg > 0),
    height_cm                   DECIMAL(5,2) CHECK (height_cm > 0),
    blood_pressure_systolic     INTEGER CHECK (blood_pressure_systolic > 0),
    blood_pressure_diastolic    INTEGER CHECK (blood_pressure_diastolic > 0),
    blood_sugar_mg_dl           DECIMAL(6,2) CHECK (blood_sugar_mg_dl >= 0),
    cholesterol_mg_dl           DECIMAL(6,2) CHECK (cholesterol_mg_dl >= 0),
    allergies                   TEXT[],
    chronic_conditions          TEXT[],
    notes                       TEXT,
    recorded_at                 TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    created_at                  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at                  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE public.health_data IS 'PKE-2: Data Kesehatan Dasar — baseline health metrics per patient';

-- ============================================================
-- 3. EXAMINATION_TYPES — PKE-4: Katalog Jenis Pemeriksaan
--    Catalog of all available health examination types
-- ============================================================
CREATE TABLE IF NOT EXISTS public.examination_types (
    id                          UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name                        TEXT NOT NULL,
    category                    TEXT NOT NULL,   -- e.g., 'Lab', 'Radiologi', 'Umum', 'Spesialis'
    description                 TEXT,
    recommended_frequency       TEXT,            -- e.g., 'Setiap 6 bulan', 'Setiap tahun'
    recommended_age_min         INTEGER,
    recommended_age_max         INTEGER,
    recommended_gender          TEXT CHECK (recommended_gender IN ('Laki-laki', 'Perempuan', 'Semua')),
    preparation_notes           TEXT,
    duration_minutes            INTEGER DEFAULT 30,
    is_active                   BOOLEAN NOT NULL DEFAULT TRUE,
    created_at                  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at                  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE public.examination_types IS 'PKE-4: Katalog Jenis Pemeriksaan — master catalog of examination types';

-- ============================================================
-- 4. APPOINTMENTS — PKE-5 & PKE-8: Perencanaan & Pengelolaan Jadwal
--    Patient appointment scheduling and management
-- ============================================================
CREATE TABLE IF NOT EXISTS public.appointments (
    id                      UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id                 UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    examination_type_id     UUID REFERENCES public.examination_types(id) ON DELETE SET NULL,
    title                   TEXT NOT NULL,
    scheduled_at            TIMESTAMPTZ NOT NULL,
    doctor_name             TEXT,
    clinic_name             TEXT,
    location                TEXT,
    status                  TEXT NOT NULL DEFAULT 'dijadwalkan'
                                CHECK (status IN ('dijadwalkan', 'dikonfirmasi', 'selesai', 'dibatalkan')),
    notes                   TEXT,
    created_at              TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at              TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE public.appointments IS 'PKE-5 & PKE-8: Jadwal Pemeriksaan — patient appointment scheduling';

-- ============================================================
-- 5. REMINDERS — PKE-9 & PKE-10: Pengingat Otomatis
--    Automated reminders for appointments and health checks
-- ============================================================
CREATE TABLE IF NOT EXISTS public.reminders (
    id                  UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id             UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    appointment_id      UUID REFERENCES public.appointments(id) ON DELETE SET NULL,
    title               TEXT NOT NULL,
    message             TEXT,
    remind_at           TIMESTAMPTZ NOT NULL,
    repeat_type         TEXT NOT NULL DEFAULT 'sekali'
                            CHECK (repeat_type IN ('sekali', 'harian', 'mingguan', 'bulanan')),
    is_sent             BOOLEAN NOT NULL DEFAULT FALSE,
    is_dismissed        BOOLEAN NOT NULL DEFAULT FALSE,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE public.reminders IS 'PKE-9 & PKE-10: Pengingat Otomatis — appointment and health reminders';

-- ============================================================
-- 6. EXAMINATION_RESULTS — PKE-11: Pencatatan Hasil Pemeriksaan
--    Records the outcomes of completed health examinations
-- ============================================================
CREATE TABLE IF NOT EXISTS public.examination_results (
    id                      UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id                 UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    appointment_id          UUID REFERENCES public.appointments(id) ON DELETE SET NULL,
    examination_type_id     UUID REFERENCES public.examination_types(id) ON DELETE SET NULL,
    examination_date        DATE NOT NULL,
    doctor_name             TEXT,
    clinic_name             TEXT,
    findings                TEXT,
    diagnosis               TEXT,
    recommendations         TEXT,
    attachment_urls         TEXT[],          -- Supabase Storage URLs
    created_at              TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at              TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE public.examination_results IS 'PKE-11: Hasil Pemeriksaan — records and documents examination outcomes';

-- ============================================================
-- 7. HEALTH_RECORDS — PKE-12: Riwayat Kesehatan
--    Time-series health metric records for trend tracking
-- ============================================================
CREATE TABLE IF NOT EXISTS public.health_records (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id         UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    record_type     TEXT NOT NULL CHECK (record_type IN (
                        'tekanan_darah', 'gula_darah', 'kolesterol',
                        'berat_badan', 'tinggi_badan', 'detak_jantung',
                        'suhu_tubuh', 'saturasi_oksigen', 'lainnya'
                    )),
    value_numeric   DECIMAL(10,2),
    value_text      TEXT,
    unit            TEXT,
    notes           TEXT,
    recorded_at     TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE public.health_records IS 'PKE-12: Riwayat Kesehatan — time-series health metrics for trend analysis';

-- ============================================================
-- 8. SCHEDULE_RECOMMENDATIONS — PKE-3: Rekomendasi Jadwal
--    System-generated examination schedule recommendations
-- ============================================================
CREATE TABLE IF NOT EXISTS public.schedule_recommendations (
    id                      UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id                 UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    examination_type_id     UUID NOT NULL REFERENCES public.examination_types(id) ON DELETE CASCADE,
    recommended_date        DATE NOT NULL,
    reason                  TEXT,
    priority                TEXT NOT NULL DEFAULT 'sedang'
                                CHECK (priority IN ('rendah', 'sedang', 'tinggi', 'darurat')),
    is_dismissed            BOOLEAN NOT NULL DEFAULT FALSE,
    is_converted            BOOLEAN NOT NULL DEFAULT FALSE,  -- became an appointment
    created_at              TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at              TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE public.schedule_recommendations IS 'PKE-3: Rekomendasi Jadwal — AI/rule-based examination schedule suggestions';

-- ============================================================
-- 9. HEALTH_INSIGHTS — PKE-15: Insight dan Evaluasi
--    Generated insights and health evaluations for patients
-- ============================================================
CREATE TABLE IF NOT EXISTS public.health_insights (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id         UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    insight_type    TEXT NOT NULL CHECK (insight_type IN (
                        'tren_positif', 'tren_negatif', 'peringatan',
                        'pencapaian', 'saran', 'evaluasi'
                    )),
    title           TEXT NOT NULL,
    content         TEXT NOT NULL,
    metric_type     TEXT,       -- which health metric this insight relates to
    is_read         BOOLEAN NOT NULL DEFAULT FALSE,
    generated_at    TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE public.health_insights IS 'PKE-15: Insight dan Evaluasi — health trend insights and evaluations';

-- ============================================================
-- 10. MOTIVATIONS — PKE-16: Panel Motivasi Pengguna
--     Motivational content shown to patients
-- ============================================================
CREATE TABLE IF NOT EXISTS public.motivations (
    id          UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    title       TEXT NOT NULL,
    content     TEXT NOT NULL,
    category    TEXT NOT NULL CHECK (category IN (
                    'umum', 'olahraga', 'nutrisi', 'mental',
                    'pencegahan', 'pemulihan'
                )),
    author      TEXT,
    image_url   TEXT,
    is_active   BOOLEAN NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE public.motivations IS 'PKE-16: Panel Motivasi — motivational content for patients';

-- ============================================================
-- 11. ADMIN_LOGS — PKE-17: Panel Monitoring Admin
--     Admin activity and system monitoring logs
-- ============================================================
CREATE TABLE IF NOT EXISTS public.admin_logs (
    id          UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    admin_id    UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    action      TEXT NOT NULL,
    target_type TEXT,       -- e.g., 'user', 'appointment', 'examination_type'
    target_id   UUID,
    details     JSONB,
    ip_address  TEXT,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE public.admin_logs IS 'PKE-17: Panel Monitoring Admin — audit trail for admin actions';

-- ============================================================
-- INDEXES for performance
-- ============================================================
CREATE INDEX IF NOT EXISTS idx_health_data_user_id ON public.health_data(user_id);
CREATE INDEX IF NOT EXISTS idx_appointments_user_id ON public.appointments(user_id);
CREATE INDEX IF NOT EXISTS idx_appointments_scheduled_at ON public.appointments(scheduled_at);
CREATE INDEX IF NOT EXISTS idx_appointments_status ON public.appointments(status);
CREATE INDEX IF NOT EXISTS idx_reminders_user_id ON public.reminders(user_id);
CREATE INDEX IF NOT EXISTS idx_reminders_remind_at ON public.reminders(remind_at);
CREATE INDEX IF NOT EXISTS idx_examination_results_user_id ON public.examination_results(user_id);
CREATE INDEX IF NOT EXISTS idx_health_records_user_id ON public.health_records(user_id);
CREATE INDEX IF NOT EXISTS idx_health_records_type ON public.health_records(record_type);
CREATE INDEX IF NOT EXISTS idx_health_records_recorded_at ON public.health_records(recorded_at);
CREATE INDEX IF NOT EXISTS idx_schedule_recommendations_user_id ON public.schedule_recommendations(user_id);
CREATE INDEX IF NOT EXISTS idx_health_insights_user_id ON public.health_insights(user_id);
CREATE INDEX IF NOT EXISTS idx_health_insights_is_read ON public.health_insights(is_read);
CREATE INDEX IF NOT EXISTS idx_admin_logs_admin_id ON public.admin_logs(admin_id);
CREATE INDEX IF NOT EXISTS idx_admin_logs_created_at ON public.admin_logs(created_at);

-- ============================================================
-- AUTO-UPDATE updated_at via trigger
-- ============================================================
CREATE OR REPLACE FUNCTION public.handle_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER trg_profiles_updated_at
    BEFORE UPDATE ON public.profiles
    FOR EACH ROW EXECUTE FUNCTION public.handle_updated_at();

CREATE OR REPLACE TRIGGER trg_health_data_updated_at
    BEFORE UPDATE ON public.health_data
    FOR EACH ROW EXECUTE FUNCTION public.handle_updated_at();

CREATE OR REPLACE TRIGGER trg_examination_types_updated_at
    BEFORE UPDATE ON public.examination_types
    FOR EACH ROW EXECUTE FUNCTION public.handle_updated_at();

CREATE OR REPLACE TRIGGER trg_appointments_updated_at
    BEFORE UPDATE ON public.appointments
    FOR EACH ROW EXECUTE FUNCTION public.handle_updated_at();

CREATE OR REPLACE TRIGGER trg_reminders_updated_at
    BEFORE UPDATE ON public.reminders
    FOR EACH ROW EXECUTE FUNCTION public.handle_updated_at();

CREATE OR REPLACE TRIGGER trg_examination_results_updated_at
    BEFORE UPDATE ON public.examination_results
    FOR EACH ROW EXECUTE FUNCTION public.handle_updated_at();

CREATE OR REPLACE TRIGGER trg_schedule_recommendations_updated_at
    BEFORE UPDATE ON public.schedule_recommendations
    FOR EACH ROW EXECUTE FUNCTION public.handle_updated_at();

CREATE OR REPLACE TRIGGER trg_motivations_updated_at
    BEFORE UPDATE ON public.motivations
    FOR EACH ROW EXECUTE FUNCTION public.handle_updated_at();

-- ============================================================
-- AUTO-CREATE PROFILE on new user signup (auth hook)
-- ============================================================
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO public.profiles (id, name, age, gender, role)
    VALUES (
        NEW.id,
        COALESCE(NEW.raw_user_meta_data->>'name', split_part(NEW.email, '@', 1)),
        COALESCE((NEW.raw_user_meta_data->>'age')::INTEGER, 1),
        COALESCE(NEW.raw_user_meta_data->>'gender', 'Lainnya'),
        COALESCE(NEW.raw_user_meta_data->>'role', 'pasien')
    );
    RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

CREATE OR REPLACE TRIGGER trg_on_auth_user_created
    AFTER INSERT ON auth.users
    FOR EACH ROW EXECUTE FUNCTION public.handle_new_user();

-- ============================================================
-- ROW LEVEL SECURITY (RLS) POLICIES
-- ============================================================

-- Enable RLS on all tables
ALTER TABLE public.profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.health_data ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.examination_types ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.appointments ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.reminders ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.examination_results ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.health_records ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.schedule_recommendations ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.health_insights ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.motivations ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.admin_logs ENABLE ROW LEVEL SECURITY;

-- profiles: users see/edit only their own; admins see all
CREATE POLICY "profiles: users manage own" ON public.profiles
    FOR ALL USING (auth.uid() = id);
CREATE POLICY "profiles: admins see all" ON public.profiles
    FOR SELECT USING (
        EXISTS (SELECT 1 FROM public.profiles WHERE id = auth.uid() AND role = 'admin')
    );

-- health_data: users manage own only
CREATE POLICY "health_data: users manage own" ON public.health_data
    FOR ALL USING (auth.uid() = user_id);
CREATE POLICY "health_data: admins see all" ON public.health_data
    FOR SELECT USING (
        EXISTS (SELECT 1 FROM public.profiles WHERE id = auth.uid() AND role = 'admin')
    );

-- examination_types: everyone can read, only admins can write
CREATE POLICY "examination_types: public read" ON public.examination_types
    FOR SELECT USING (TRUE);
CREATE POLICY "examination_types: admins write" ON public.examination_types
    FOR ALL USING (
        EXISTS (SELECT 1 FROM public.profiles WHERE id = auth.uid() AND role = 'admin')
    );

-- appointments: users manage own
CREATE POLICY "appointments: users manage own" ON public.appointments
    FOR ALL USING (auth.uid() = user_id);
CREATE POLICY "appointments: admins see all" ON public.appointments
    FOR SELECT USING (
        EXISTS (SELECT 1 FROM public.profiles WHERE id = auth.uid() AND role = 'admin')
    );

-- reminders: users manage own
CREATE POLICY "reminders: users manage own" ON public.reminders
    FOR ALL USING (auth.uid() = user_id);

-- examination_results: users manage own
CREATE POLICY "examination_results: users manage own" ON public.examination_results
    FOR ALL USING (auth.uid() = user_id);
CREATE POLICY "examination_results: admins see all" ON public.examination_results
    FOR SELECT USING (
        EXISTS (SELECT 1 FROM public.profiles WHERE id = auth.uid() AND role = 'admin')
    );

-- health_records: users manage own
CREATE POLICY "health_records: users manage own" ON public.health_records
    FOR ALL USING (auth.uid() = user_id);

-- schedule_recommendations: users manage own
CREATE POLICY "schedule_recommendations: users manage own" ON public.schedule_recommendations
    FOR ALL USING (auth.uid() = user_id);

-- health_insights: users read own
CREATE POLICY "health_insights: users read own" ON public.health_insights
    FOR ALL USING (auth.uid() = user_id);

-- motivations: everyone can read, only admins can write
CREATE POLICY "motivations: public read" ON public.motivations
    FOR SELECT USING (is_active = TRUE);
CREATE POLICY "motivations: admins write" ON public.motivations
    FOR ALL USING (
        EXISTS (SELECT 1 FROM public.profiles WHERE id = auth.uid() AND role = 'admin')
    );

-- admin_logs: only admins
CREATE POLICY "admin_logs: admins only" ON public.admin_logs
    FOR ALL USING (
        EXISTS (SELECT 1 FROM public.profiles WHERE id = auth.uid() AND role = 'admin')
    );

-- ============================================================
-- SEED: Examination Types Catalog (PKE-4)
-- ============================================================
INSERT INTO public.examination_types (name, category, description, recommended_frequency, recommended_age_min, recommended_age_max, recommended_gender, duration_minutes) VALUES
('Cek Darah Lengkap', 'Lab', 'Pemeriksaan darah lengkap untuk mendeteksi anemia, infeksi, dan kondisi lain', 'Setiap tahun', 18, 100, 'Semua', 30),
('Cek Gula Darah', 'Lab', 'Pemeriksaan kadar glukosa darah untuk deteksi diabetes', 'Setiap 6 bulan', 30, 100, 'Semua', 15),
('Cek Kolesterol', 'Lab', 'Pemeriksaan kadar kolesterol total, LDL, HDL, dan trigliserida', 'Setiap tahun', 20, 100, 'Semua', 30),
('Cek Tekanan Darah', 'Umum', 'Pengukuran tekanan darah sistolik dan diastolik', 'Setiap 3 bulan', 18, 100, 'Semua', 10),
('Rontgen Dada', 'Radiologi', 'Foto rontgen dada untuk deteksi masalah paru-paru dan jantung', 'Setiap 2 tahun', 18, 100, 'Semua', 20),
('Pap Smear', 'Spesialis', 'Skrining kanker serviks untuk wanita', 'Setiap 3 tahun', 21, 65, 'Perempuan', 30),
('Mammografi', 'Radiologi', 'Skrining kanker payudara dengan sinar-X', 'Setiap 2 tahun', 40, 74, 'Perempuan', 45),
('EKG (Elektrokardiogram)', 'Spesialis', 'Pemeriksaan aktivitas listrik jantung', 'Setiap tahun', 40, 100, 'Semua', 30),
('Pemeriksaan Mata', 'Spesialis', 'Tes ketajaman penglihatan dan kesehatan mata', 'Setiap 2 tahun', 18, 100, 'Semua', 45),
('Pemeriksaan Gigi', 'Spesialis', 'Pembersihan dan pemeriksaan gigi rutin', 'Setiap 6 bulan', 5, 100, 'Semua', 60),
('USG Abdomen', 'Radiologi', 'Ultrasonografi organ perut (hati, ginjal, kantung empedu)', 'Setiap 2 tahun', 30, 100, 'Semua', 30),
('Cek Fungsi Ginjal', 'Lab', 'Pemeriksaan kreatinin, ureum, dan asam urat', 'Setiap tahun', 30, 100, 'Semua', 30),
('Cek Fungsi Hati', 'Lab', 'Pemeriksaan SGOT, SGPT, dan bilirubin', 'Setiap tahun', 18, 100, 'Semua', 30),
('Vaksinasi Influenza', 'Umum', 'Imunisasi flu tahunan', 'Setiap tahun', 6, 100, 'Semua', 15),
('Pemeriksaan Umum (MCU)', 'Umum', 'Medical Check-Up komprehensif tahunan', 'Setiap tahun', 18, 100, 'Semua', 180);

-- ============================================================
-- SEED: Sample Motivations (PKE-16)
-- ============================================================
INSERT INTO public.motivations (title, content, category, author) VALUES
('Kesehatan adalah Investasi Terbaik', 'Menjaga kesehatan hari ini berarti Anda berinvestasi untuk masa depan yang lebih cerah. Setiap langkah kecil menuju gaya hidup sehat adalah kemenangan besar.', 'umum', 'MEDISLOT'),
('Gerak Setiap Hari', '30 menit berjalan kaki setiap hari dapat mengurangi risiko penyakit jantung hingga 35%. Mulailah dari langkah kecil dan rasakan perbedaannya!', 'olahraga', 'MEDISLOT'),
('Makan Seimbang, Hidup Seimbang', 'Piring makan yang seimbang — setengahnya sayur dan buah, seperempat protein, seperempat karbohidrat — adalah kunci energi optimal sepanjang hari.', 'nutrisi', 'MEDISLOT'),
('Kelola Stres dengan Bijak', 'Stres adalah bagian dari hidup, namun cara Anda mengelolanya yang menentukan kesehatan Anda. Coba teknik pernapasan dalam atau meditasi 10 menit sehari.', 'mental', 'MEDISLOT'),
('Cegah Sebelum Mengobati', 'Pemeriksaan kesehatan rutin dapat mendeteksi penyakit lebih awal ketika masih mudah diobati. Jadwalkan check-up Anda hari ini!', 'pencegahan', 'MEDISLOT'),
('Anda Lebih Kuat dari yang Anda Kira', 'Perjalanan pemulihan membutuhkan waktu dan kesabaran. Rayakan setiap kemajuan kecil — Anda melakukan hal luar biasa!', 'pemulihan', 'MEDISLOT');
