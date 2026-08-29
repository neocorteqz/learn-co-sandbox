const defaultTheme = {
  mode: 'dark',
  bg: '#0a1220',
  surface: '#111d2f',
  surfaceAlt: '#182842',
  panel: '#0f1b2d',
  text: '#e8f2ff',
  muted: '#9cb4d1',
  border: '#93b6ff33',
  accent: '#6ee7ff',
  accentStrong: '#4fc3f7',
  accentSoft: '#6ee7ff2e',
  buttonBgStart: '#44d3ff',
  buttonBgEnd: '#5d9bff',
  buttonText: '#09131c',
  buttonRadius: 12,
  dropdownBg: '#162338',
  dropdownText: '#edf6ff',
  dropdownRadius: 10,
  bannerHeight: 132,
  bannerBg: '#6ee7ff29',
  logoSize: 96,
  logoUrl: 'data:image/svg+xml;utf8,' + encodeURIComponent(`
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 180 180">
      <defs>
        <linearGradient id="g" x1="0" x2="1">
          <stop stop-color="#6ee7ff"/>
          <stop offset="1" stop-color="#5d9bff"/>
        </linearGradient>
      </defs>
      <rect width="180" height="180" rx="36" fill="#0c1b2e"/>
      <path d="M24 118 90 42l66 76-18 18H42l-18-18Z" fill="url(#g)" opacity="0.92"/>
      <path d="M64 80h52v34H64z" fill="#0c1b2e" opacity="0.88"/>
      <path d="M90 52 132 90 90 128 48 90 90 52Z" fill="none" stroke="#dff9ff" stroke-width="8"/>
      <circle cx="90" cy="90" r="10" fill="#dff9ff"/>
    </svg>
  `),
};

const themePresets = {
  dark: {
    bg: '#0a1220',
    surface: '#111d2f',
    surfaceAlt: '#182842',
    panel: '#0f1b2d',
    text: '#e8f2ff',
    muted: '#9cb4d1',
    border: '#93b6ff33',
    accent: '#6ee7ff',
    accentStrong: '#4fc3f7',
    accentSoft: '#6ee7ff2e',
    buttonBgStart: '#44d3ff',
    buttonBgEnd: '#5d9bff',
    buttonText: '#09131c',
    dropdownBg: '#162338',
    dropdownText: '#edf6ff',
    bannerBg: '#6ee7ff29',
  },
  light: {
    bg: '#edf5ff',
    surface: '#f8fbff',
    surfaceAlt: '#dfeeff',
    panel: '#ffffff',
    text: '#13243d',
    muted: '#5d7290',
    border: '#bfd5f8',
    accent: '#2b7fff',
    accentStrong: '#195fd6',
    accentSoft: '#dfeeff',
    buttonBgStart: '#4aa6ff',
    buttonBgEnd: '#466dff',
    buttonText: '#f7fbff',
    dropdownBg: '#edf4ff',
    dropdownText: '#1c2b3f',
    bannerBg: '#d9ebff',
  },
};

const state = { ...defaultTheme };

const root = document.documentElement;
const output = document.getElementById('cssOutput');
const logoImg = document.getElementById('brandLogo');

const fields = {
  bg: document.getElementById('bgColor'),
  surface: document.getElementById('surfaceColor'),
  surfaceAlt: document.getElementById('surfaceAltColor'),
  panel: document.getElementById('panelColor'),
  text: document.getElementById('textColor'),
  muted: document.getElementById('mutedColor'),
  border: document.getElementById('borderColor'),
  accent: document.getElementById('accentColor'),
  accentStrong: document.getElementById('accentStrongColor'),
  accentSoft: document.getElementById('accentSoftColor'),
  buttonBgStart: document.getElementById('buttonStartColor'),
  buttonBgEnd: document.getElementById('buttonEndColor'),
  buttonText: document.getElementById('buttonTextColor'),
  dropdownBg: document.getElementById('dropdownBgColor'),
  dropdownText: document.getElementById('dropdownTextColor'),
  bannerBg: document.getElementById('bannerBgColor'),
  buttonRadius: document.getElementById('buttonRadius'),
  dropdownRadius: document.getElementById('dropdownRadius'),
  bannerHeight: document.getElementById('bannerHeight'),
  logoSize: document.getElementById('logoSize'),
  logoUrl: document.getElementById('logoUrl'),
};

function applyThemePreset(mode) {
  const preset = themePresets[mode];
  if (!preset) return;

  Object.entries(preset).forEach(([key, value]) => {
    if (key in state) state[key] = value;
  });

  syncControls();
  updateTheme();
}

function syncControls() {
  Object.entries(fields).forEach(([key, el]) => {
    if (!el) return;
    if (key === 'buttonRadius' || key === 'dropdownRadius' || key === 'bannerHeight' || key === 'logoSize') {
      el.value = state[key];
      return;
    }
    if (key === 'logoUrl') {
      el.value = state.logoUrl;
      return;
    }
    el.value = state[key];
  });

  document.querySelectorAll('.theme-btn').forEach((button) => {
    const isActive = button.dataset.mode === state.mode;
    button.classList.toggle('active', isActive);
  });
}

function updateTheme() {
  root.style.setProperty('--bg', state.bg);
  root.style.setProperty('--surface', state.surface);
  root.style.setProperty('--surface-alt', state.surfaceAlt);
  root.style.setProperty('--panel', state.panel);
  root.style.setProperty('--text', state.text);
  root.style.setProperty('--muted', state.muted);
  root.style.setProperty('--border', state.border);
  root.style.setProperty('--accent', state.accent);
  root.style.setProperty('--accent-strong', state.accentStrong);
  root.style.setProperty('--accent-soft', state.accentSoft);
  root.style.setProperty('--button-bg', `linear-gradient(135deg, ${state.buttonBgStart}, ${state.buttonBgEnd})`);
  root.style.setProperty('--button-text', state.buttonText);
  root.style.setProperty('--button-radius', `${state.buttonRadius}px`);
  root.style.setProperty('--dropdown-bg', state.dropdownBg);
  root.style.setProperty('--dropdown-text', state.dropdownText);
  root.style.setProperty('--dropdown-radius', `${state.dropdownRadius}px`);
  root.style.setProperty('--banner-height', `${state.bannerHeight}px`);
  root.style.setProperty('--banner-bg', `linear-gradient(135deg, ${state.accentSoft}, ${state.bannerBg})`);
  root.style.setProperty('--logo-size', `${state.logoSize}px`);

  const safeLogo = state.logoUrl && state.logoUrl.trim() ? state.logoUrl : defaultTheme.logoUrl;
  logoImg.src = safeLogo;

  output.value = `:root {
  --bg: ${state.bg};
  --surface: ${state.surface};
  --surface-alt: ${state.surfaceAlt};
  --panel: ${state.panel};
  --text: ${state.text};
  --muted: ${state.muted};
  --border: ${state.border};
  --accent: ${state.accent};
  --accent-strong: ${state.accentStrong};
  --accent-soft: ${state.accentSoft};
  --button-bg: linear-gradient(135deg, ${state.buttonBgStart}, ${state.buttonBgEnd});
  --button-text: ${state.buttonText};
  --button-radius: ${state.buttonRadius}px;
  --dropdown-bg: ${state.dropdownBg};
  --dropdown-text: ${state.dropdownText};
  --dropdown-radius: ${state.dropdownRadius}px;
  --banner-height: ${state.bannerHeight}px;
  --banner-bg: linear-gradient(135deg, ${state.accentSoft}, ${state.bannerBg});
  --logo-size: ${state.logoSize}px;
  --banner-logo: url("${safeLogo}");
}`;
}

function bindField(key, handler) {
  const field = fields[key];
  if (!field) return;
  field.addEventListener('input', (event) => {
    const value = event.target.value;
    state[key] = key.includes('Radius') || key.includes('Height') || key.includes('Size') ? Number(value) : value;
    if (key === 'logoUrl') {
      state.logoUrl = value;
    }
    if (key === 'accentSoft') {
      state.accentSoft = value;
    }
    updateTheme();
  });
}

Object.keys(fields).forEach((key) => bindField(key));

document.querySelectorAll('.theme-btn').forEach((button) => {
  button.addEventListener('click', () => {
    state.mode = button.dataset.mode;
    applyThemePreset(state.mode);
  });
});

document.getElementById('resetTheme').addEventListener('click', () => {
  Object.assign(state, { ...defaultTheme, mode: 'dark' });
  applyThemePreset('dark');
});

document.getElementById('copyCss').addEventListener('click', async () => {
  await navigator.clipboard.writeText(output.value);
  const button = document.getElementById('copyCss');
  const original = button.textContent;
  button.textContent = 'Copied!';
  setTimeout(() => {
    button.textContent = original;
  }, 1200);
});

applyThemePreset('dark');
