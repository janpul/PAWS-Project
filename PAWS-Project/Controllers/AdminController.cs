using PAWSProject.Models;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.Mvc;

namespace PAWSProject.Controllers
{
    public class AdminController : Controller
    {

        private PawsContext db = new PawsContext();

        public ActionResult Index()
        {
            if (Session["IsAuthenticated"] == null || !(bool)Session["IsAuthenticated"])
            {
                Session["ErrorMessage"] = "You must be logged in to access this webpage.";
                return RedirectToAction("Login");
            }
            return View();
        }
        public ActionResult Error404()
        {
            if (Session["IsAuthenticated"] == null || !(bool)Session["IsAuthenticated"])
            {
                Session["ErrorMessage"] = "You must be logged in to access this webpage.";
                return RedirectToAction("Login");
            }
            return View();
        }
        public ActionResult Manage()
        {
            if (Session["IsAuthenticated"] == null || !(bool)Session["IsAuthenticated"])
            {
                Session["ErrorMessage"] = "You must be logged in to access this webpage.";
                return RedirectToAction("Login");
            }
            return View();
        }

        public ActionResult User()
        {
            if (Session["IsAuthenticated"] == null || !(bool)Session["IsAuthenticated"])
            {
                Session["ErrorMessage"] = "You must be logged in to access this webpage.";
                return RedirectToAction("Login");
            }
            return View();
        }
        public ActionResult Login()
        {
            ViewBag.ErrorMessage = Session["ErrorMessage"];
            Session["ErrorMessage"] = null; // Clear the error message after retrieving it
            return View();
        }

        [HttpPost]
        public JsonResult Login(string username, string password)
        {
            var user = db.tbluser.FirstOrDefault(u => u.username == username);
            if (user != null)
            {
                if (user.isActive == 0)
                {
                    return Json(new { success = false, message = "Your account is inactive. Please contact the administrator." });
                }

                if (string.Equals(user.username, username, StringComparison.Ordinal) && PasswordHelper.VerifyPassword(password, user.password))
                {
                    Session["IsAuthenticated"] = true;
                    Session["Username"] = user.username;
                    Session["AccessLevel"] = user.accessLvl;
                    return Json(new { success = true });
                }
            }

            return Json(new { success = false, message = "Invalid username or password" });
        }

        [HttpGet]
        public JsonResult UsernameCheck(string username)
        {
            var userExists = db.tbluser.Any(u => u.username == username);
            return Json(userExists, JsonRequestBehavior.AllowGet);
        }

        [HttpGet]
        public JsonResult SearchUsers(string search = "")
        {
            List<tbluserModel> users;

            if (string.IsNullOrEmpty(search))
            {
                users = db.tbluser.ToList();  // Fetch all users if search is empty or null
            }
            else
            {
                users = db.tbluser.Where(u => u.username.Contains(search)).ToList();  // Filter users by username
            }

            return Json(users, JsonRequestBehavior.AllowGet);
        }

        [HttpPost]
        public JsonResult CreateUser(tbluserModel user)
        {
            user.password = PasswordHelper.HashPassword(user.password); // Hash the password
            user.createdAt = DateTime.Now;
            user.updateAt = DateTime.Now;
            user.isActive = 1;
            db.tbluser.Add(user);
            db.SaveChanges();
            return Json(user);
        }


        [HttpPut]
        public JsonResult UpdateUser(tbluserModel user)
        {
            var existingUser = db.tbluser.Find(user.userID);
            if (existingUser != null)
            {
                existingUser.username = user.username;
                existingUser.accessLvl = user.accessLvl;
                existingUser.isActive = user.isActive;
                existingUser.updateAt = DateTime.Now;

                // Only update the password if a new one is provided
                if (!string.IsNullOrEmpty(user.password))
                {
                    existingUser.password = PasswordHelper.HashPassword(user.password);
                }

                db.SaveChanges();

                if (Session["Username"] != null && Session["Username"].ToString() == existingUser.username)
                {
                    Session["AccessLevel"] = existingUser.accessLvl;
                }

                return Json(new { success = true });
            }
            return Json(new { success = false, message = "User not found" });
        }



        [HttpDelete]
        public JsonResult DeleteUser(int id)
        {
            var user = db.tbluser.Find(id);
            if (user != null)
            {
                db.tbluser.Remove(user);
                db.SaveChanges();
            }
            return Json(user);
        }

        [HttpPost]
        public ActionResult AddPet(tblpetModel pet, HttpPostedFileBase image)
        {
            if (ModelState.IsValid)
            {
                AddPetToDatabase(pet, image);
                return RedirectToAction("Index");
            }

            return View("Index");
        }

        private void AddPetToDatabase(tblpetModel pet, HttpPostedFileBase image)
        {
            var dateNow = DateTime.Now;

            if (image != null && image.ContentLength > 0)
            {
                var fileName = System.IO.Path.GetFileName(image.FileName);
                var path = System.IO.Path.Combine(Server.MapPath("~/Content/img/adopt/"), fileName);
                image.SaveAs(path);
                pet.imagePath = "/Content/img/adopt/" + fileName;
            }

            pet.createdAt = dateNow;
            pet.updateAt = dateNow;
            pet.encodedBy = Session["Username"].ToString();
            pet.status = "For Adoption";

            using (var connect = new PawsContext())
            {
                connect.tblpet.Add(pet);
                connect.SaveChanges();
            }
        }

        public ActionResult GetPets()
        {
            var pets = db.tblpet.ToList();
            return Json(pets, JsonRequestBehavior.AllowGet);
        }

        public ActionResult SearchPets(string query)
        {
            var pets = db.tblpet.Where(p => p.name.Contains(query)).ToList();
            return Json(pets, JsonRequestBehavior.AllowGet);
        }

        [HttpPost]
        public ActionResult DeletePet(int petID)
        {
            var pet = db.tblpet.Find(petID);
            if (pet != null)
            {
                db.tblpet.Remove(pet);
                db.SaveChanges();
            }
            return Json(new { success = true });
        }

        [HttpPost]
        public ActionResult EditPet(tblpetModel pet, HttpPostedFileBase image)
        {
            var existingPet = db.tblpet.Find(pet.petID);
            if (existingPet != null)
            {
                existingPet.name = pet.name;
                existingPet.gender = pet.gender;
                existingPet.age = pet.age;
                existingPet.size = pet.size;
                existingPet.details = pet.details;
                existingPet.status = pet.status;
                existingPet.updateAt = DateTime.Now;
                existingPet.encodedBy = Session["Username"].ToString();

                if (image != null && image.ContentLength > 0)
                {
                    var fileName = System.IO.Path.GetFileName(image.FileName);
                    var path = System.IO.Path.Combine(Server.MapPath("~/Content/img/adopt/"), fileName);
                    image.SaveAs(path);
                    existingPet.imagePath = "/Content/img/adopt/" + fileName;
                }

                db.SaveChanges();
            }
            return Json(new { success = true });
        }

        public ActionResult Logout()
        {
            Session.Clear();
            return RedirectToAction("Login");
        }
    }
}