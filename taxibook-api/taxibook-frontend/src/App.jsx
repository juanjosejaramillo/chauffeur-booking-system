import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import BookingWizard from './components/BookingWizard/BookingWizard';
import HomePage from './pages/HomePage';

function App() {
  return (
    <Router>
      <div className="min-h-screen bg-gray-50">
        <Routes>
          <Route path="/" element={<HomePage />} />
          <Route path="/book" element={<BookingWizard />} />
        </Routes>
      </div>
    </Router>
  );
}

export default App;