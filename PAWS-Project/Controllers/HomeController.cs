using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.Mvc;

namespace PAWS_Project.Controllers
{
    public class HomeController : Controller
    {
        public ActionResult Index()
        {
            return View();
        }

        public ActionResult About()
        {
            ViewBag.Message = "Your application description page.";

            return View();
        }

        public ActionResult Contact()
        {
            ViewBag.Message = "Your contact page.";

            return View();
        }

        public ActionResult Donate()
        {
            ViewBag.Message = "Donate Page.";

            return View();
        }

        public ActionResult DonateBilling()
        {
            ViewBag.Message = "Donate Page.";

            return View();
        }

        public ActionResult Adopt()
        {
            ViewBag.Message = "Adopt Page.";

            return View();
        }

        public ActionResult Advocacy()
        {
            ViewBag.Message = "Advocacy Page.";

            return View();
        }

        public ActionResult AdvocacyNews1()
        {
            ViewBag.Message = "Advocacy Page.";

            return View();
        }

        public ActionResult LocalServices()
        {
            ViewBag.Message = "Local Services Page.";

            return View();
        }
    }
}