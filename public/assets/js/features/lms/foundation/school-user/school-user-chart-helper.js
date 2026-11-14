// ROLE COLORS (STATIC)
const SCHOOL_USER_ROLE_COLORS = {
    'Siswa': '#10b981',
    'Guru': '#3b82f6',
    'Orang Tua': '#f59e0b',
    'Kepala Sekolah': '#ef4444',
    'Wakil Kepala Sekolah': '#8b5cf6',
    'Wakil Kesiswaan': '#ec4899',
    'Admin Sekolah': '#64748b'
};

// FALLBACK ROLE COLORS
const SCHOOL_USER_FALLBACK_COLORS = [

    '#14b8a6',
    '#f97316',
    '#6366f1',
    '#84cc16',
    '#a855f7',
    '#0ea5e9',
    '#e11d48',
    '#78716c',
    '#d946ef',
    '#0891b2',
    '#65a30d',
    '#ea580c',
    '#4f46e5',
    '#16a34a',
    '#c026d3',
    '#0284c7',
    '#ca8a04',
    '#dc2626',
    '#7c3aed',
    '#475569'

];

const dynamicSchoolUserRoleColors = {};

// GET ROLE COLOR
function getSchoolUserRoleColor(role) {
    if (SCHOOL_USER_ROLE_COLORS[role]) {
        return SCHOOL_USER_ROLE_COLORS[role];
    }

    if (!dynamicSchoolUserRoleColors[role]) {
        const usedColors = Object.values(dynamicSchoolUserRoleColors);
        const availableColor = SCHOOL_USER_FALLBACK_COLORS.find(color => !usedColors.includes(color));

        if (availableColor) {
            dynamicSchoolUserRoleColors[role] = availableColor;

        } else {
            const index = Object.keys(dynamicSchoolUserRoleColors).length % SCHOOL_USER_FALLBACK_COLORS.length;
            dynamicSchoolUserRoleColors[role] = SCHOOL_USER_FALLBACK_COLORS[index];
        }
    }
    return dynamicSchoolUserRoleColors[role];
}


/// GET CHART CONFIG
function getSchoolUserChartConfig() {
    const container = document.getElementById('container');

    if (!container) {
        return null;
    }

    const role = container.dataset.role;
    const foundationId = container.dataset.foundationId;

    if (!role || !foundationId || foundationId === 'undefined' || foundationId === 'null') {
        return null;
    }

    return {
        role,
        foundationId
    };
}


// DESTROY CHART
function destroySchoolUserChart(chart) {
    if (chart) {
        chart.destroy();
    }

    return null;
}

// GET CHART ELEMENTS
function getSchoolUserChartElements(type) {
    return {
        canvas: document.getElementById(`school-user-by-${type}-chart`),
        loading: document.getElementById(`school-user-by-${type}-loading`),
        empty: document.getElementById(`school-user-by-${type}-empty`)
    };
}

// RESET CHART STATE
function resetSchoolUserChartState(canvas, loading, empty) {
    if (loading) {
        loading.classList.remove('hidden');
    }

    if (empty) {
        empty.classList.add('hidden');
        empty.classList.remove('flex');
    }

    if (canvas) {
        canvas.classList.add('hidden');
    }
}

// SHOW CHART
function showSchoolUserChart(canvas) {
    if (!canvas) {
        return;
    }

    canvas.classList.remove('hidden');
}

// SHOW EMPTY
function showSchoolUserEmpty(canvas, empty) {
    if (canvas) {
        canvas.classList.add('hidden');
    }

    if (empty) {
        empty.classList.remove('hidden');
        empty.classList.add('flex');
    }
}

// HIDE LOADING
function hideSchoolUserLoading(loading) {
    if (!loading) {
        return;
    }

    loading.classList.add('hidden');
}

// SORT ROLES
function sortSchoolUserRoles(roles) {
    const fixedRoles = Object.keys(SCHOOL_USER_ROLE_COLORS);
    const detectedRoles = Array.isArray(roles) ? roles : [];
    const fixed = fixedRoles.filter(role => detectedRoles.includes(role));
    const dynamic = detectedRoles.filter(role => !fixedRoles.includes(role)).sort((a, b) => a.localeCompare(b));
    return [...fixed, ...dynamic];
}

// EXTRACT ROLES FROM SCHOOL DATA
function extractSchoolUserRoles(data) {
    const roleSet = new Set();

    data.forEach(school => {
        if (school.roles && typeof school.roles === 'object') {
            Object.keys(school.roles).forEach(role => {
                roleSet.add(role);
            });
        }
    });

    return Array.from(roleSet);
}