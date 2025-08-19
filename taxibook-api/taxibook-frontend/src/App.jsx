import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import BookingWizard from './components/BookingWizard/BookingWizard';
import TipPayment from './pages/TipPayment';
import TipSuccess from './pages/TipSuccess';
import TipAlready from './pages/TipAlready';

function App() {
  return (
    <Router>
      <div className="min-h-screen bg-gray-50">
        <Routes>
          <Route path="/" element={<BookingWizard />} />
          <Route path="/tip/:token" element={<TipPayment />} />
          <Route path="/tip/:token/success" element={<TipSuccess />} />
          <Route path="/tip/:token/already-tipped" element={<TipAlready />} />
        </Routes>
      </div>
    </Router>
  );
}

export default App;